import React, { createContext, useContext, useEffect, useMemo, useReducer, useState } from 'react';
import SectionTitle from '../components/SectionTitle';
import Selector from '../components/Selector';
import Str from '../components/Str';
import { useString } from '../lib/hooks';
import { genClassName } from '../lib/style';
import { AppContext } from './lib/context';
import { useCourseActivitiesWithCompletion } from './lib/hooks';
import { Activity, Course, CourseRule, CourseRules, CmRule, Module, GlobalRules, Defaults } from './lib/types';

function getActivityName(activity: Activity) {
    return `${activity.name} (${activity.module})`;
}

const AddCourseRule: React.FC<{ courses: Course[]; disabled?: boolean; onAdd: (courseId: number) => void }> = ({
    courses,
    onAdd,
    disabled,
}) => {
    const addCourseStr = useString('addcourseellipsis');
    const handleAdd = (id: any) => {
        onAdd(id);
    };
    return (
        <Selector
            disabled={disabled}
            options={courses.map((c) => ({
                value: c.id,
                label: c.displayname,
            }))}
            placeholder={addCourseStr}
            onAdd={handleAdd}
        />
    );
};

const AddActivityRule: React.FC<{ cms: Activity[]; disabled?: boolean; onAdd: (cmId: number) => void }> = ({
    cms,
    onAdd,
    disabled,
}) => {
    const addActivityStr = useString('addactivityellipsis');
    const handleAdd = (id: any) => {
        onAdd(id);
    };
    return (
        <Selector
            disabled={disabled}
            options={cms.map((c) => ({
                value: c.cmid,
                label: getActivityName(c),
            }))}
            placeholder={addActivityStr}
            onAdd={handleAdd}
        />
    );
};

const GlobalRulesWidget: React.FC<{
    rules: GlobalRules;
    defaults: Defaults;
    modules: Module[];
    expanded: number[];
    useRecommended: boolean;
}> = ({ rules, modules, expanded, useRecommended, defaults }) => {
    const { setExpanded, setCollapsed, setGlobalUsesrecommended, updateGlobalCourseCompleted, updateGlobalModuleCompleted } =
        useReducerAction();

    const isExpanded = expanded.includes(0);
    const handleExpandedChange = (v: boolean) => {
        if (v) setExpanded(0);
        else setCollapsed(0);
    };

    const modulesByName = useMemo(() => {
        return (rules.modules || []).reduce<Record<string, number>>((carry, rule) => {
            return {
                ...carry,
                [rule.module]: rule.coins || 0,
            };
        }, {});
    }, [rules.modules]);

    const courseCoinsValue = useRecommended ? defaults.course || 0 : rules.course;

    return (
        <div className="">
            <div className={[genClassName('section'), isExpanded ? '' : 'expanded'].join(' ')}>
                <SectionTitle title={<Str id="globalsettings" />} onExpandedChange={handleExpandedChange} expanded={isExpanded} />
                {isExpanded ? (
                    <div className={genClassName('section-content')}>
                        <div style={{ paddingTop: '.5rem' }}>
                            <label style={{ margin: 0 }}>
                                <input
                                    type="checkbox"
                                    checked={useRecommended}
                                    onChange={(e) => setGlobalUsesrecommended(e.target.checked, defaults)}
                                    style={{ marginRight: '.5rem' }}
                                />
                                <Str id="userecommended" />
                            </label>
                        </div>
                        <Item
                            label={<Str id="completingacourse" />}
                            onChange={(coins) => updateGlobalCourseCompleted(coins || 0)}
                            value={courseCoinsValue}
                            disabled={useRecommended}
                        />
                        {modules.map((mod) => {
                            const value = (useRecommended ? defaults.modules[mod.module] : modulesByName[mod.module]) || 0;
                            return (
                                <Item
                                    key={mod.module}
                                    label={<Str id="completingn" a={mod.name} />}
                                    disabled={useRecommended}
                                    onChange={(coins) => updateGlobalModuleCompleted(mod.module, coins || 0)}
                                    value={value}
                                />
                            );
                        })}
                    </div>
                ) : null}
            </div>
        </div>
    );
};

const CoursesRules: React.FC<{
    rules: CourseRules;
    courses: Course[];
    expanded: number[];
}> = ({ rules, courses, expanded }) => {
    const { addCourse, setExpanded, setCollapsed } = useReducerAction();

    const courseIds = rules.reduce<number[]>((carry, r) => {
        if (carry.indexOf(r.id) < 0) {
            carry.push(r.id);
        }
        return carry;
    }, []);
    const availableCourses = courses.filter((t) => !courseIds.includes(t.id));
    const byCourseId = rules.reduce<{ [index: number]: CourseRule }>((carry, rule) => {
        carry[rule.id] = rule;
        return carry;
    }, {});

    return (
        <div className="">
            {courseIds.map((courseId) => {
                const course = courses.find((c) => c.id === courseId);
                const rules = byCourseId[courseId];
                const isExpanded = expanded.includes(courseId) && Boolean(course);
                const handleExpandedChange = (v: boolean) => {
                    if (v) setExpanded(courseId);
                    else setCollapsed(courseId);
                };
                return (
                    <div className={[genClassName('section'), isExpanded ? '' : 'expanded'].join(' ')} key={courseId}>
                        <SectionTitle
                            title={course?.displayname || <Str id="unknowncoursen" a={courseId} />}
                            onExpandedChange={handleExpandedChange}
                            expanded={isExpanded}
                        />
                        {isExpanded && course ? (
                            <div className={genClassName('section-content')}>
                                <CourseRules rule={rules} id={courseId} />
                            </div>
                        ) : null}
                    </div>
                );
            })}
            <AddCourseRule courses={availableCourses} onAdd={addCourse} />
        </div>
    );
};

const CourseRules: React.FC<{
    rule: CourseRule;
    id: number;
}> = ({ rule, id }) => {
    const { isError, isSuccess, activities } = useCourseActivitiesWithCompletion(id);
    const { addCm, updateCourseCompleted, updateCmCompleted } = useReducerAction();

    if (isError) {
        return <div className="my-4">Error loading</div>;
    } else if (!isSuccess) {
        return <div className="my-4">Loading...</div>;
    }

    const activitiesByCmId = activities.map((l) => l.cmid);
    const cmIds: number[] = (rule.cms || [])
        .map((l) => l.id)
        .sort((l1, l2) => {
            const idx1 = activitiesByCmId.indexOf(l1);
            const idx2 = activitiesByCmId.indexOf(l2);
            if (idx2 < 0) return 1;
            if (idx1 < 0) return 1;
            return idx1 - idx2;
        });
    const byCmId = (rule.cms || []).reduce<{ [index: number]: CmRule }>((carry, rule) => {
        carry[rule.id] = rule;
        return carry;
    }, {});

    const handleaddCm = (cmId: number) => {
        addCm(id, cmId);
    };

    const handleCourseCompletionChange = (coins: number | null) => {
        updateCourseCompleted(id, coins);
    };

    return (
        <div>
            <Item label={<Str id="coursecompletion" />} onChange={handleCourseCompletionChange} value={rule.coins} />
            <div className={genClassName('section-items')}>
                {cmIds.map((cmId) => {
                    const activity = activities.find((l) => l.cmid === cmId);
                    const cmRules = byCmId[cmId];

                    const handleLessonCompletionChange = (coins: number | null) => {
                        updateCmCompleted(id, cmId, coins);
                    };

                    return (
                        <Item
                            key={cmId}
                            label={`${activity ? getActivityName(activity) : <Str id="unknownactivityn" a={cmId} />} completion`}
                            value={cmRules.coins}
                            onChange={handleLessonCompletionChange}
                        />
                    );
                })}
            </div>
            <div className="my-4">
                <AddActivityRule cms={activities.filter((item) => !cmIds.includes(item.cmid))} onAdd={handleaddCm} />
            </div>
        </div>
    );
};

const Item = ({
    label,
    value = null,
    onChange,
    disabled,
}: {
    label: React.ReactNode;
    value?: number | string | null;
    onChange: (v: number | null) => void;
    disabled?: boolean;
}) => {
    const defaultParensStr = useString('defaultparens');
    const [localValue, setLocalValue] = useState<string | undefined | null | number>(value);
    useEffect(() => setLocalValue(value), [value, setLocalValue]);

    const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
        if (disabled) return;
        const val = parseInt(e.target.value);
        const finalVal = isNaN(val) ? null : Math.max(0, val);
        onChange(finalVal);
        setLocalValue(finalVal);
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (disabled) return;
        setLocalValue(e.target.value);
    };

    const isDefault = localValue !== 0 && !localValue;
    const displayValue = localValue === 0 || localValue ? localValue.toString() : '';

    return (
        <div className={genClassName('rule-item')}>
            <label className="" style={{ margin: 0 }}>
                <div className={genClassName('rule-item-label')}>{label}</div>
                <div className={genClassName('rule-item-field')}>
                    <input
                        type="text"
                        value={displayValue}
                        onBlur={handleBlur}
                        onChange={handleChange}
                        placeholder={isDefault ? defaultParensStr : ''}
                        className="form-control"
                        disabled={disabled}
                    />
                </div>
            </label>
        </div>
    );
};

type Action =
    | 'addCourse'
    | 'addCm'
    | 'setGlobalUsesrecommended'
    | 'setExpanded'
    | 'setCollapsed'
    | 'updateCourseCompleted'
    | 'updateGlobalCourseCompleted'
    | 'updateGlobalModuleCompleted'
    | 'updateCmCompleted';

function globalRulesReducer(state: GlobalRules, [type, payload]: [Action, any]) {
    if (type === 'setGlobalUsesrecommended') {
        let newState = state;
        const defaults = payload.defaults as Defaults;
        if (typeof state.course === 'undefined') {
            newState = { ...newState, course: defaults.course };
        }
        const hasDefaults = Boolean(Object.keys(defaults.modules).length);
        if (hasDefaults && (!newState.modules || !newState.modules.length)) {
            newState = {
                ...newState,
                modules: Object.keys(defaults.modules).map((module) => ({
                    module,
                    coins: defaults.modules[module] || 0,
                })),
            };
        }
        if (newState !== state) return newState;
    }
    if (type === 'updateGlobalCourseCompleted') {
        return {
            ...state,
            course: payload.coins,
        };
    }
    if (type === 'updateGlobalModuleCompleted') {
        const modules = state.modules || [];
        let moduleRule = modules.find((rule) => {
            return rule.module === payload.module;
        }) || { module: payload.module, coins: 0 };
        return {
            ...state,
            modules: modules
                .filter((rule) => rule !== moduleRule)
                .concat([
                    {
                        ...moduleRule,
                        coins: payload.coins,
                    },
                ]),
        };
    }
    return state;
}

function rulesReducer(state: CourseRules, [type, payload]: [Action, any]) {
    console.log(type, payload);
    if (type === 'addCourse') {
        return [
            ...state,
            {
                id: payload,
                coins: null,
            } as CourseRule,
        ];
    } else if (type === 'addCm') {
        return state.map((course) => {
            if (course.id !== payload.courseId) return course;
            return {
                ...course,
                cms: [...(course.cms || []), { id: payload.cmId, coins: null } as CmRule],
            };
        });
    } else if (type === 'updateCourseCompleted') {
        return state.map((course) => {
            if (course.id !== payload.courseId) return course;
            return {
                ...course,
                coins: payload.coins,
            };
        });
    } else if (type === 'updateCmCompleted') {
        return state.map((course) => {
            if (course.id !== payload.courseId) return course;
            return {
                ...course,
                cms: (course.cms || []).map((cm) => {
                    if (cm.id !== payload.cmId) return cm;
                    return {
                        ...cm,
                        coins: payload.coins,
                    };
                }),
            };
        });
    }

    return state;
}

type State = {
    rules: CourseRules;
    globalRules: GlobalRules;
    globalUsesRecommended: boolean;
    expanded: number[];
};

function reducer(state: State, action: [Action, any]) {
    const rules = rulesReducer(state.rules, action);
    if (rules !== state.rules) {
        state = {
            ...state,
            rules,
        };
    }

    const globalRules = globalRulesReducer(state.globalRules, action);
    if (globalRules !== state.globalRules) {
        state = {
            ...state,
            globalRules,
        };
    }

    const [type, payload] = action;
    switch (type) {
        case 'addCourse':
        case 'setExpanded':
            return {
                ...state,
                expanded: [...state.expanded, payload],
            };
        case 'setCollapsed':
            return {
                ...state,
                expanded: state.expanded.filter((id) => id !== payload),
            };
        case 'setGlobalUsesrecommended':
            return {
                ...state,
                globalUsesRecommended: payload.useRecommended,
            };
    }

    return state;
}

const ReducerActionsContext = createContext({
    addCourse: (courseId: number) => {},
    addCm: (courseId: number, cmId: number) => {},
    setCollapsed: (courseId: number) => {},
    setExpanded: (courseId: number) => {},
    setGlobalUsesrecommended: (useRecommended: boolean, defaults: Defaults) => {},
    updateCourseCompleted: (courseId: number, coins: number | null) => {},
    updateCmCompleted: (courseId: number, cmId: number, coins: number | null) => {},
    updateGlobalCourseCompleted: (coins: number) => {},
    updateGlobalModuleCompleted: (module: string, coins: number) => {},
});

const useReducerAction = () => {
    return useContext(ReducerActionsContext);
};

const App = ({ rules = [], globalRules = {} }: { rules?: CourseRules; globalRules?: GlobalRules }) => {
    const [state, dispatch] = useReducer(reducer, {
        rules,
        globalRules,
        globalUsesRecommended:
            // No global rules.
            (typeof globalRules.course === 'undefined' || globalRules.course === null) &&
            // No module rules.
            (!globalRules.modules ||
                !globalRules.modules?.filter((mod) => {
                    return typeof mod.coins !== 'undefined' && mod.coins !== null;
                }).length),
        expanded: [],
    });
    const { modules, courses, defaults } = useContext(AppContext);

    return (
        <>
            <ReducerActionsContext.Provider
                value={{
                    addCourse: (courseId: number) => dispatch(['addCourse', courseId]),
                    addCm: (courseId: number, cmId: number) => dispatch(['addCm', { courseId, cmId }]),
                    setCollapsed: (courseId: number) => dispatch(['setCollapsed', courseId]),
                    setExpanded: (courseId: number) => dispatch(['setExpanded', courseId]),
                    setGlobalUsesrecommended: (useRecommended: boolean, defaults: Defaults) =>
                        dispatch(['setGlobalUsesrecommended', { useRecommended, defaults }]),
                    updateCourseCompleted: (courseId: number, coins: number | null) =>
                        dispatch(['updateCourseCompleted', { courseId, coins }]),
                    updateGlobalCourseCompleted: (coins: number | null) => dispatch(['updateGlobalCourseCompleted', { coins }]),
                    updateGlobalModuleCompleted: (module: string, coins: number | null) =>
                        dispatch(['updateGlobalModuleCompleted', { module, coins }]),
                    updateCmCompleted: (courseId: number, cmId: number, coins: number | null) =>
                        dispatch(['updateCmCompleted', { courseId, cmId, coins }]),
                }}
            >
                <GlobalRulesWidget
                    rules={state.globalRules}
                    modules={modules}
                    defaults={defaults}
                    expanded={state.expanded}
                    useRecommended={state.globalUsesRecommended}
                />
                <CoursesRules rules={state.rules} courses={courses} expanded={state.expanded} />
            </ReducerActionsContext.Provider>
        </>
    );
};

const AppContainer = ({
    rules,
    courses = [],
    modules = [],
    defaults = { course: 0, modules: {} },
}: {
    rules?: CourseRules;
    courses?: Course[];
    modules?: Module[];
    defaults: Defaults;
}) => {
    return (
        <AppContext.Provider
            value={{
                courses,
                modules: modules.sort((m1, m2) => {
                    return m1.name.localeCompare(m2.name);
                }),
                defaults,
            }}
        >
            <App rules={rules} />
        </AppContext.Provider>
    );
};

export default AppContainer;
