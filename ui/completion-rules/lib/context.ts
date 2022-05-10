import { createContext } from 'react';
import { Course, Defaults, Module } from './types';

export const AppContext = createContext<{
    courses: Course[];
    modules: Module[];
    defaults: Defaults;
}>({
    courses: [],
    modules: [],
    defaults: { course: 0, modules: {} },
});
