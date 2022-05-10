import React from 'react';
import { useStrings } from '../lib/hooks';
import { imageUrl } from '../lib/moodle';
import { genClassName } from '../lib/style';

const SectionTitle: React.FC<{ expanded: boolean; title: React.ReactNode; onExpandedChange: (expanded: boolean) => void }> = ({
    expanded,
    title,
    onExpandedChange,
}) => {
    const getString = useStrings(['collapse', 'expand'], 'core');
    const imgUrl = imageUrl(expanded ? 't/expanded' : 't/collapsed', 'core');
    const alt = expanded ? getString('collapse') : getString('expand');
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
