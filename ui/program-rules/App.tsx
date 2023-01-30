import React, { createContext, useContext, useReducer } from 'react';
import { useMutation } from 'react-query';
import Button from '../components/Button';
import { RuleItem as Item } from '../components/RuleItem';
import SectionTitle from '../components/SectionTitle';
import Selector from '../components/Selector';
import Str from '../components/Str';
import { useString, useUnloadCheck } from '../lib/hooks';
import { getModule } from '../lib/moodle';
import { genClassName } from '../lib/style';
import { AppContext } from './lib/context';
import { Defaults, GlobalRules, Program, ProgramRule, ProgramRules, ProgramRules as ProgramsRules } from './lib/types';

const AddProgramRule: React.FC<{ programs: Program[]; disabled?: boolean; onAdd: (programId: number) => void }> = ({
  programs,
  onAdd,
  disabled,
}) => {
  const addProgramStr = useString('addprogramellipsis');
  const handleAdd = (id: any) => {
    onAdd(id);
  };
  return (
    <Selector
      disabled={disabled}
      options={programs.map((c) => ({
        value: c.id,
        label: c.displayname,
      }))}
      placeholder={addProgramStr}
      onAdd={handleAdd}
    />
  );
};

const GlobalRulesWidget: React.FC<{
  rules: GlobalRules;
  defaults: Defaults;
  expanded: number[];
  useRecommended: boolean;
}> = ({ rules, expanded, useRecommended, defaults }) => {
  const { setExpanded, setCollapsed, setGlobalUsesrecommended, updateGlobalProgramCompleted } = useReducerAction();

  const isExpanded = expanded.includes(0);
  const handleExpandedChange = (v: boolean) => {
    if (v) setExpanded(0);
    else setCollapsed(0);
  };

  const programCoinsValue = (useRecommended ? defaults.program : rules.program) || 0;

  return (
    <div style={{ marginTop: '1.5rem' }}>
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
              label={<Str id="completingaprogram" />}
              onChange={(coins) => updateGlobalProgramCompleted(coins || 0)}
              value={programCoinsValue}
              disabled={useRecommended}
              allowNull={false}
            />
          </div>
        ) : null}
      </div>
    </div>
  );
};

const ProgramsRules: React.FC<{
  rules: ProgramsRules;
  programs: Program[];
  expanded: number[];
}> = ({ rules, programs, expanded }) => {
  const { addProgram, removeProgram, setExpanded, setCollapsed } = useReducerAction();
  const areYouSureStr = useString('areyousure', 'core');

  const programIds = rules.reduce<number[]>((carry, r) => {
    if (carry.indexOf(r.id) < 0) {
      carry.push(r.id);
    }
    return carry;
  }, []);
  const availablePrograms = programs.filter((t) => !programIds.includes(t.id));
  const byProgramId = rules.reduce<{ [index: number]: ProgramRule }>((carry, rule) => {
    carry[rule.id] = rule;
    return carry;
  }, {});

  return (
    <div>
      {programIds.map((programId) => {
        const program = programs.find((c) => c.id === programId);
        const rules = byProgramId[programId];
        const isExpanded = expanded.includes(programId) && Boolean(program);
        const handleExpandedChange = (v: boolean) => {
          if (v) setExpanded(programId);
          else setCollapsed(programId);
        };
        const handleDelete = () => {
          if (confirm(areYouSureStr)) {
            removeProgram(programId);
          }
        };
        return (
          <div className={[genClassName('section'), isExpanded ? '' : 'expanded'].join(' ')} key={programId}>
            <SectionTitle
              title={program?.displayname || <Str id="unknownprogramn" a={programId} />}
              onExpandedChange={handleExpandedChange}
              onDelete={handleDelete}
              expanded={isExpanded}
            />
            {isExpanded && program ? (
              <div className={genClassName('section-content')}>
                <ProgramRules rule={rules} id={programId} />
              </div>
            ) : null}
          </div>
        );
      })}
      <AddProgramRule programs={availablePrograms} onAdd={addProgram} />
    </div>
  );
};

const ProgramRules: React.FC<{
  rule: ProgramRule;
  id: number;
}> = ({ rule, id }) => {
  const { updateProgramCompleted } = useReducerAction();

  const handleCourseCompletionChange = (coins: number | null) => {
    updateProgramCompleted(id, coins);
  };

  return (
    <div>
      <Item label={<Str id="programcompletion" />} onChange={handleCourseCompletionChange} value={rule.coins} />
    </div>
  );
};


type Action =
  | 'addProgram'
  | 'removeProgram'
  | 'setDirty'
  | 'setExpanded'
  | 'setGlobalUsesrecommended'
  | 'setCollapsed'
  | 'updateProgramCompleted'
  | 'updateGlobalProgramCompleted';

function globalRulesReducer(state: GlobalRules, [type, payload]: [Action, any]) {
  if (type === 'setGlobalUsesrecommended') {
    let newState = state;
    const defaults = payload.defaults as Defaults;
    if (typeof newState.program === 'undefined' || newState.program === null) {
      newState = { ...newState, program: defaults.program };
    }
    if (newState !== state) return newState;
  }
  if (type === 'updateGlobalProgramCompleted') {
    return {
      ...state,
      program: payload.coins,
    };
  }
  return state;
}

function rulesReducer(state: ProgramRules, [type, payload]: [Action, any]) {
  if (type === 'addProgram') {
    return [
      ...state,
      {
        id: payload,
        coins: null,
      } as ProgramRule,
    ];
  } else if (type === 'removeProgram') {
    return state.filter((c: ProgramRule) => c.id !== payload);
  } else if (type === 'updateProgramCompleted') {
    return state.map((program: ProgramRule) => {
      if (program.id !== payload.programId) return program;
      return {
        ...program,
        coins: payload.coins,
      };
    });
  }
  return state;
}

type State = {
  rules: ProgramsRules;
  globalRules: GlobalRules;
  globalUsesRecommended: boolean;
  expanded: number[];
  isDirty: boolean;
};

function reducer(state: State, action: [Action, any]) {
  const rules = rulesReducer(state.rules, action);
  if (rules !== state.rules) {
    state = {
      ...state,
      rules,
      isDirty: true,
    };
  }

  const globalRules = globalRulesReducer(state.globalRules, action);
  if (globalRules !== state.globalRules) {
    state = {
      ...state,
      globalRules,
      isDirty: true,
    };
  }

  const [type, payload] = action;
  switch (type) {
    case 'addProgram':
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
    case 'setDirty':
      return {
        ...state,
        isDirty: payload,
      };
    case 'setGlobalUsesrecommended':
      return {
        ...state,
        globalUsesRecommended: payload.useRecommended,
        isDirty: true,
      };
  }

  return state;
}

const ReducerActionsContext = createContext({
  addProgram: (programId: number) => {},
  removeProgram: (programId: number) => {},
  setCollapsed: (programId: number) => {},
  setExpanded: (programId: number) => {},
  setGlobalUsesrecommended: (useRecommended: boolean, defaults: Defaults) => {},
  updateProgramCompleted: (programId: number, coins: number | null) => {},
  updateGlobalProgramCompleted: (coins: number) => {},
});

const useReducerAction = () => {
  return useContext(ReducerActionsContext);
};

const App = ({ rules = [], globalRules = {} }: { rules?: ProgramsRules; globalRules?: GlobalRules }) => {
  const [state, dispatch] = useReducer(reducer, {
    rules,
    globalRules,
    globalUsesRecommended:
      // No global rules.
      typeof globalRules.program === 'undefined' || globalRules.program === null,
    expanded: [],
    isDirty: false,
  });
  const { programs, defaults } = useContext(AppContext);
  useUnloadCheck(state.isDirty);

  const mutation = useMutation(
    () => {
      return getModule('core/ajax').call([
        {
          methodname: 'block_motrain_save_program_rules',
          args: {
            global: state.globalUsesRecommended ? { userecommended: true } : state.globalRules,
            rules: state.rules,
          },
        },
      ])[0];
    },
    { onError: (err) => getModule('core/notification').exception(err), onSuccess: () => dispatch(['setDirty', false]) }
  );
  const handleSaveAll = () => {
    mutation.mutate();
  };

  return (
    <>
      <ReducerActionsContext.Provider
        value={{
          addProgram: (programId: number) => dispatch(['addProgram', programId]),
          removeProgram: (programId: number) => dispatch(['removeProgram', programId]),

          setCollapsed: (programId: number) => dispatch(['setCollapsed', programId]),

          setExpanded: (programId: number) => dispatch(['setExpanded', programId]),
          setGlobalUsesrecommended: (useRecommended: boolean, defaults: Defaults) =>
            dispatch(['setGlobalUsesrecommended', { useRecommended, defaults }]),
          updateProgramCompleted: (programId: number, coins: number | null) =>
            dispatch(['updateProgramCompleted', { programId, coins }]),
          updateGlobalProgramCompleted: (coins: number | null) => dispatch(['updateGlobalProgramCompleted', { coins }]),
        }}
      >
        <GlobalRulesWidget
          rules={state.globalRules}
          defaults={defaults}
          expanded={state.expanded}
          useRecommended={state.globalUsesRecommended}
        />
        <ProgramsRules rules={state.rules} programs={programs} expanded={state.expanded} />

        <div style={{ marginTop: '1rem' }}>
          <div>
            <Button onClick={handleSaveAll} primary disabled={mutation.isLoading}>
              {!mutation.isLoading ? <Str id="saverules" /> : <Str id="saving" />}
            </Button>
          </div>
        </div>
      </ReducerActionsContext.Provider>
    </>
  );
};

const AppContainer = ({
  rules,
  globalRules,
  programs = [],
  defaults = { program: 0 },
}: {
  rules?: ProgramRules;
  globalRules?: GlobalRules;
  programs?: Program[];
  defaults: Defaults;
}) => {
  return (
    <AppContext.Provider
      value={{
        programs,
        defaults,
      }}
    >
      <App rules={rules} globalRules={globalRules} />
    </AppContext.Provider>
  );
};

export default AppContainer;
