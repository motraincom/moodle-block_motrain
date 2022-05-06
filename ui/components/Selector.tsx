import React, { ReactNode, useEffect, useState } from 'react';
import Select from 'react-select';

import type { GetOptionValue, GetOptionLabel, SingleValue, ActionMeta } from 'react-select';
import { genClassName } from '../lib/style';

type Option = { value: any; label: string };
const defaultGetOptionLabel = (option: Option) => option.label;
const defaultGetOptionValue = (option: Option) => option.value;

const Selector: React.FC<{
    options: Option[];
    onAdd: (value: any) => void;
    getOptionLabel?: GetOptionLabel<Option>;
    getOptionValue?: GetOptionValue<Option>;
    placeholder?: ReactNode;
}> = ({ options, onAdd, getOptionLabel = defaultGetOptionLabel, getOptionValue = defaultGetOptionValue, placeholder }) => {
    const [selected, setSelected] = useState<Option | null>(null);

    useEffect(() => {
        setSelected(null);
    }, [options]);

    const handleAdd = (option: Option) => onAdd(getOptionValue(option));

    const handleChange = (v: SingleValue<Option>) => {
        setSelected(v);
        if (v) handleAdd(v);
    };

    return (
        <div className={genClassName('selector')}>
            <Select
                className={genClassName('react-select-container')}
                classNamePrefix={genClassName('react-select')}
                getOptionLabel={getOptionLabel || defaultGetOptionLabel}
                getOptionValue={getOptionValue || defaultGetOptionValue}
                menuShouldScrollIntoView={false}
                onChange={handleChange}
                options={options}
                placeholder={placeholder}
                value={selected}
            />
        </div>
    );
};

export default Selector;
