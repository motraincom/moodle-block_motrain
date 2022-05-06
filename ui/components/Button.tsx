import React from 'react';

const Button: React.FC<{ primary?: boolean; disabled?: boolean; onClick?: () => void }> = ({ primary, ...props }) => {
    let classNames = 'btn';
    if (primary) {
        classNames += ' btn-primary';
    } else {
        classNames += ' btn-default btn-secondary';
    }
    return <button className={classNames} {...props} />;
};

export default Button;
