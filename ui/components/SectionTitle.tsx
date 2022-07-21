import React from 'react';
import { useStrings } from '../lib/hooks';
import { imageUrl } from '../lib/moodle';
import { genClassName } from '../lib/style';

const SectionTitle: React.FC<{
    expanded: boolean;
    title: React.ReactNode;
    onDelete?: () => void;
    onExpandedChange: (expanded: boolean) => void;
}> = ({ expanded, title, onExpandedChange, onDelete }) => {
    const getString = useStrings(['collapse', 'expand', 'delete'], 'core');
    const imgUrl = imageUrl(expanded ? 't/expanded' : 't/collapsed', 'core');
    const alt = expanded ? getString('collapse') : getString('expand');
    const handleClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
        e.preventDefault();
        e.stopPropagation();
        onExpandedChange(!expanded);
    };
    const handleDeleteClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
        e.preventDefault();
        e.stopPropagation();
        onDelete && onDelete();
    };
    return (
        <div className={genClassName('section-title')}>
            <a onClick={handleClick} role="button">
                <img src={imgUrl} alt={alt} className="icon" />
                {title}
            </a>
            {onDelete && expanded ? (
                <div>
                    <a onClick={handleDeleteClick} role="button" href="#">
                        <img src={imageUrl('t/delete', 'core')} alt={getString('delete')} className="icon" />
                    </a>
                </div>
            ) : null}
        </div>
    );
};

export default SectionTitle;
