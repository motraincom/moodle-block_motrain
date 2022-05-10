export type Course = { id: number; displayname: string };

export type Activity = {
    cmid: number;
    contextid: number;
    name: string;
    module: string;
};

export type Defaults = {
    course: number; // Course completion coins.
    modules: Record<string, number>; // Completion coins per module name.
};

export type Module = {
    module: string;
    name: string;
};

export type CourseRule = {
    id: number;
    coins: number | null;
    cms?: CmRule[];
};

export type CourseRules = CourseRule[];

export type CmRule = {
    id: number;
    coins: number | null;
};

export type GlobalRules = {
    course?: number | null; // Course completed coins.
    modules?: ModuleRule[];
};

export type ModuleRule = {
    module: string; // Module name.
    coins: number | null;
};
