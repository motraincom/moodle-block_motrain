import { useEffect, useState } from "react";
import { useString } from "../lib/hooks";
import { imageUrl } from "../lib/moodle";
import { genClassName } from "../lib/style";

export const RuleItem = ({
    label,
    value = null,
    onChange,
    onDelete,
    disabled,
    allowNull = true,
  }: {
    label: React.ReactNode;
    value?: number | string | null;
    onChange: (v: number | null) => void;
    onDelete?: () => void;
    allowNull?: boolean;
    disabled?: boolean;
  }) => {
    const defaultParensStr = useString('defaultparens');
    const deleteStr = useString('delete', 'core');
    const [localValue, setLocalValue] = useState<string | undefined | null | number>(value);
    useEffect(() => setLocalValue(value), [value, setLocalValue]);

    const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
      if (disabled) return;
      const val = parseInt(e.target.value);
      const finalVal = isNaN(val) ? (allowNull ? null : 0) : Math.max(0, val);
      onChange(finalVal);
      setLocalValue(finalVal);
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      if (disabled) return;
      setLocalValue(e.target.value);
    };

    const handleDeleteClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
      e.preventDefault();
      onDelete && onDelete();
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
              placeholder={allowNull && isDefault ? defaultParensStr : ''}
              className="form-control"
              disabled={disabled}
            />
          </div>
        </label>
        {onDelete ? (
          <div>
            <a className={genClassName('rule-item-delete')} onClick={handleDeleteClick} role="button" href="#">
              <img src={imageUrl('t/delete', 'core')} alt={deleteStr} className="icon" />
            </a>
          </div>
        ) : null}
      </div>
    );
  };