import gql from "graphql-tag";

export function updateMessage(_root, variables, {cache}) {
    cache.writeData({
        id: 'State',
        data: {
            message: ''
        }
    });
    cache.writeData({
        id: 'State',
        data: {
            message: variables.message
        }
    });
    return null;
}

const ToggleNotificationsOpenTabFragment = gql`
    fragment csrfToken on State {
        notificationsTabOpen
    }
`;

export function toggleNotificationsTabOpen(_root, _variables, {cache}): boolean {
    let open = cache.readFragment({fragment: ToggleNotificationsOpenTabFragment, id: 'State'}).notificationsTabOpen;
    cache.writeData({
        id: 'State',
        data: {
            notificationsTabOpen: !open
        }
    });
    return open;
}