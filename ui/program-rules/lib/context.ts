import { createContext } from 'react';
import { Defaults, Program } from './types';

export const AppContext = createContext<{
    programs: Program[];
    defaults: Defaults;
}>({
    programs: [],
    defaults: { program: 0 },
});
