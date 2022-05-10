import { useQuery } from 'react-query';
import { getModule } from '../../lib/moodle';

export const useCourseActivitiesWithCompletion = (courseId: number) => {
    const q = useQuery(
        ['course-activities-with-completion', courseId],
        async () => {
            return getModule('core/ajax').call([
                { methodname: 'block_motrain_get_activities_with_completion', args: { courseid: courseId } },
            ])[0] as { cmid: number; contextid: number; name: string; module: string }[];
        },
        {
            onError: (err) => getModule('core/notification').exception(err),
        }
    );

    const activities = q?.data || [];

    return { ...q, activities };
};
