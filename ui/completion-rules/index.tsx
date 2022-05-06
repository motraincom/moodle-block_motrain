import React from 'react';
import ReactDOM from 'react-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { getModule, makeDependenciesDefinition } from '../lib/moodle';
import App from './App';

const queryClient = new QueryClient({
    defaultOptions: {
        mutations: {
            onError: (err) => getModule('core/notification').exception(err),
        },
    },
});

function startApp(node: HTMLElement, props: any) {
    ReactDOM.render(
        <QueryClientProvider client={queryClient}>
            <App {...props} />
        </QueryClientProvider>,
        node
    );
}

const dependencies = makeDependenciesDefinition(['core/str', 'core/ajax', 'core/notification']);

export { dependencies, startApp };
