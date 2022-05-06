import { getString, getUrl, hasString } from '../lib/moodle';
import Str from '../components/Str';
import Button from '../components/Button';
import { genClassName } from '../lib/style';
import { useString, useStrings, useUniqueId } from '../lib/hooks';
import SectionTitle from '../components/SectionTitle';
import { ReactNode, useEffect, useMemo, useState } from 'react';
import Selector from '../components/Selector';

type Course = { id: number; displayname: string };
type Module = { name: string; module: string };
type Rule = { courseid: number; cmid: number; modname: string | null; coins: number };

const App: React.FC<{
    courses: Course[];
    modules: Module[];
    defaults: { course: number; modules: Record<string, number> };
    rules: Rule[];
}> = (props) => {
    const data = useMemo(() => {
        const courseRules = props.rules.filter((rule) => rule.courseid !== 0);
        const moduleRules = props.rules.filter((rule) => rule.courseid === 0 && rule.modname);
        return {
            courseRulesById: courseRules.reduce<Record<number, Rule[]>>(
                (carry, rule) => ({ ...carry, [rule.courseid]: [...(carry[rule.courseid] || []), rule] }),
                {}
            ),
            moduleRulesByModName: moduleRules.reduce((carry, rule) => ({ ...carry, [rule.modname as string]: rule.coins }), {}),
        };
    }, []);

    const [rules, setRules] = useState(() => data);
    const [state, setState] = useState(() => ({
        useRecommended: Object.keys(data.moduleRulesByModName).length < 1,
    }));

    const handleModuleChange = (module: string, coins: number) => {
        setRules({
            ...rules,
            moduleRulesByModName: {
                ...rules.moduleRulesByModName,
                [module]: coins,
            },
        });
    };

    const handleUseRecommendedChange = (useRecommended: boolean) => {
        if (!useRecommended && !Object.keys(rules.moduleRulesByModName).length) {
            setRules({ ...rules, moduleRulesByModName: props.defaults.modules });
        }
        setState({ ...state, useRecommended });
    };

    return (
        <>
            <GlobalSettings
                rules={state.useRecommended ? props.defaults.modules : rules.moduleRulesByModName}
                modules={props.modules}
                onChange={handleModuleChange}
                useRecommended={state.useRecommended}
                onUseRecommendedChange={handleUseRecommendedChange}
            />

            <CoursesRules
                rules={rules.courseRulesById}
                courses={props.courses}
                onAdd={() => {}}
                onUpdate={() => {}}
                onRemove={() => {}}
            />

            <div style={{ display: 'flex' }}>
                <div>
                    <a href={getUrl('/blocks/motrain/settings_rules.php')} className="btn btn-default btn-secondary">
                        {getString('cancel', 'core')}
                    </a>
                </div>
                <div>
                    <Button onClick={() => {}} primary disabled={false}>
                        <Str id="savechanges" component="core" />
                    </Button>
                </div>
            </div>
        </>
    );
};

const AddCourseTypeRule: React.FC<{ courses: Course[]; onAdd: (courseId: number) => void }> = ({ courses, onAdd }) => {
    return (
        <Selector
            options={courses.map((c: Course) => ({
                value: c.id,
                label: c.displayname,
            }))}
            placeholder={<Str id="addcourse" />}
            onAdd={onAdd}
        />
    );
};

const GlobalSettings: React.FC<{
    modules: Module[];
    rules: Record<string, number>;
    useRecommended?: boolean;
    onChange: (modname: string, coins: number) => void;
    onUseRecommendedChange: (useRecommended: boolean) => void;
}> = ({ modules, rules, ...props }) => {
    const [expanded, setExpanded] = useState(false);
    // const groupedBy = groupBy(rules, 'itemtype');
    // const availableTypes = itemTypes.filter((t) => !(t.name in groupedBy));
    const globalSettingsStr = useString('globalsettings', 'block_motrain');

    // const handleAddTypeBracket = (type) => {
    //   onAdd(createItemTypeRule(type));
    // };

    const mods = modules.sort((m1, m2) => m1.name.localeCompare(m2.name));

    return (
        <div className={genClassName(['section', expanded ? 'section-expanded' : 'section-collapsed'])}>
            <SectionTitle title={globalSettingsStr} onExpandedChange={(v: boolean) => setExpanded(v)} expanded={expanded} />
            {expanded ? (
                <>
                    <div className={genClassName('section-content')}>
                        <div style={{ margin: '1rem 0' }}>
                            <label>
                                <input
                                    type="checkbox"
                                    onChange={(e) => props.onUseRecommendedChange(e.target.checked)}
                                    checked={props.useRecommended}
                                />{' '}
                                <Str id="userecommended" />
                            </label>
                        </div>
                        <div className={genClassName('section-items')}>
                            {mods.map((mod) => {
                                return (
                                    <CoinCondition
                                        key={mod.module}
                                        name={<Str id="completingn" a={mod.name} />}
                                        coins={rules[mod.module] || 0}
                                        onChange={(coins) => props.onChange(mod.module, coins)}
                                        disabled={props.useRecommended}
                                    />
                                );
                            })}
                        </div>
                    </div>
                </>
            ) : null}
        </div>
    );
};

const isNotPlaceholderFilter = (rule: Rule) => {
    return Boolean(rule.cmid);
};

const CoursesRules: React.FC<{
    rules: Record<number, Rule[]>;
    courses: Course[];
    onAdd: () => void;
    onUpdate: () => void;
    onRemove: () => void;
}> = ({ rules, courses, onAdd, onUpdate, onRemove }) => {
    const [expanded, setExpanded] = useState<number[]>([]);

    const courseNames = useMemo<Record<number, string>>(() => {
        return courses.reduce((carry, course) => ({ ...carry, [course.id]: course.displayname }), {});
    }, [courses]);
    const courseIds = Object.keys(rules).map((x) => parseInt(x, 10));
    const availableCourses = courses.filter((t) => !(t.id in courseIds));

    return (
        <div className={genClassName('courses')}>
            {courseIds.map((courseId) => {
                const courseName = courseNames[courseId] || `Unknown (${courseId})`;
                const courseRules = rules[courseId];
                const isExpanded = expanded.includes(courseId);
                const handleExpandedChange = (v: boolean) => {
                    if (v) setExpanded([...expanded, courseId]);
                    else setExpanded(expanded.filter((id) => id != courseId));
                };
                return (
                    <div
                        className={genClassName(['section', isExpanded ? 'section-expanded' : 'section-collapsed'])}
                        key={courseId}
                    >
                        <SectionTitle title={courseName} onExpandedChange={handleExpandedChange} expanded={isExpanded} />
                        {/* {isExpanded ? (
              <CourseRules
                rules={courseRules.filter(isNotPlaceholderFilter)}
                courseId={courseId}
                onAdd={onAdd}
                onRemove={onRemove}
                onUpdate={onUpdate}
              />
            ) : null} */}
                    </div>
                );
            })}
            <AddCourseTypeRule courses={availableCourses} onAdd={onAdd} />
        </div>
    );
};

const cleanValue = (v: string) => {
    return Math.max(0, Math.round(parseInt(v, 10))) || 0;
};

const CoinCondition: React.FC<{
    coins: number;
    disabled?: boolean;
    name: string | ReactNode;
    onChange: (v: number) => void;
    onDelete?: () => void;
}> = (props) => {
    const id = useUniqueId();
    const [value, setValue] = useState(props.coins.toString());
    useEffect(() => setValue(props.coins.toString()), [props.coins]);
    return (
        <div className={genClassName('coin-condition')}>
            <div className="amount">
                <input
                    disabled={props.disabled}
                    value={value}
                    onChange={(e) => setValue(e.target.value.replace(/[^0-9]/, ''))}
                    onBlur={(e) => {
                        e.preventDefault();
                        if (props.disabled) return;
                        props.onChange(cleanValue(e.target.value));
                    }}
                    type="text"
                    className="form-control"
                    maxLength={5}
                    id={id}
                />
                <Str id="coins" />
            </div>
            <div className="condition">
                <label htmlFor={id}>{props.name}</label>
            </div>
            <div className="actions">
                {props.onDelete ? (
                    <a
                        href="#"
                        onClick={(e) => {
                            e.preventDefault();
                            props.onDelete && props.onDelete();
                        }}
                        role="button"
                    >
                        <span className="glyphicon glyphicon-remove">
                            <span className="sr-only">
                                <Str id="delete" component="core" />
                            </span>
                        </span>
                    </a>
                ) : null}
            </div>
        </div>
    );
};

export default App;
