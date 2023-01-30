export type Program = { id: number; displayname: string };

export type Defaults = {
    program: number; // Program completion coins.
};

export type ProgramRule = {
    id: number;
    coins: number | null;
};

export type ProgramRules = ProgramRule[];

export type GlobalRules = {
    program?: number | null; // Progrma completed coins.
};
