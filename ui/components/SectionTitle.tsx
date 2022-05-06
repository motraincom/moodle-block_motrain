import React from 'react';
import { getString, imageUrl } from '../lib/moodle';
import { genClassName } from '../lib/style';

const SectionTitle: React.FC<{ expanded: boolean; title: string; onExpandedChange: (expanded: boolean) => void }> = ({
    expanded,
    title,
    onExpandedChange,
}) => {
    const imgUrl = imageUrl(expanded ? 't/expanded' : 't/collapsed', 'core');
    const alt = expanded ? getString('collapse', 'core') : getString('expand', 'core');
    const handleClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
        e.preventDefault();
        e.stopPropagation();
        onExpandedChange(!expanded);
    };
    return (
        <a className={genClassName('section-title')} onClick={handleClick} role="button">
            <img src={imgUrl} alt={alt} className="icon" />
            {title}
        </a>
    );
};

export default SectionTitle;
