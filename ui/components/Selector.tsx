import React, { useEffect, useState } from 'react';
import Select, { OptionTypeBase } from 'react-select';
import { useString } from '../lib/hooks';

type Option = OptionTypeBase;
type Value = string | number;
const defaultGetOptionLabel = (option: any) => option.label || '';
const defaultGetOptionValue = (option: any) => option.value || '';

const Selector: React.FC<{
    options: Option[];
    onAdd: (value: Value) => void;
    getOptionLabel?: (option: any) => Value;
    getOptionValue?: (option: any) => Value;
    placeholder?: string;
    disabled?: boolean;
}> = ({
    options,
    onAdd,
    disabled,
    getOptionLabel = defaultGetOptionLabel,
    getOptionValue = defaultGetOptionValue,
    placeholder,
}) => {
    const noOptionsStr = useString('nooptions');
    const [selected, setSelected] = useState<Option | null>();

    useEffect(() => {
        setSelected(null);
    }, [options]);

    const handleAdd = (option: Option) => onAdd(getOptionValue(option));

    const handleChange = (v: Option | null) => {
        setSelected(v || null);
        if (!v) return;
        handleAdd(v);
    };

    return (
        <div className="block_motrain-selector">
            <Select
                className="block_motrain-react-select-container"
                classNamePrefix="block_motrain-react-select"
                getOptionLabel={getOptionLabel || defaultGetOptionLabel}
                getOptionValue={getOptionValue || defaultGetOptionValue}
                menuShouldScrollIntoView={false}
                noOptionsMessage={() => noOptionsStr}
                isDisabled={disabled}
                onChange={handleChange}
                options={options}
                placeholder={placeholder}
                value={selected}
            />
        </div>
    );
};

export default Selector;
