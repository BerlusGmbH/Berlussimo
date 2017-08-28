import {Person} from "./models";

export default class PersonMerged {
    icon: string = 'mdi-account-multiple';
    data: any;

    static typeOne(notification) {
        switch (notification.type) {
            case 'App\\Notifications\\PersonMerged':
                notification = Object.setPrototypeOf(
                    notification, PersonMerged.prototype
                );
                notification.data.left = Object.setPrototypeOf(
                    notification.data.left, Person.prototype
                );
                notification.data.right = Object.setPrototypeOf(
                    notification.data.right, Person.prototype
                );
                notification.data.merged = Object.setPrototypeOf(
                    notification.data.merged, Person.prototype
                );
                break;
        }
        return notification;
    }

    static type(notifications) {
        let keys = Object.keys(notifications);
        keys.forEach(function (key) {
            switch (notifications[key].type) {
                case 'App\\Notifications\\PersonMerged':
                    notifications[key] = Object.setPrototypeOf(
                        notifications[key], PersonMerged.prototype
                    );
                    notifications[key].data.left = Object.setPrototypeOf(
                        notifications[key].data.left, Person.prototype
                    );
                    notifications[key].data.right = Object.setPrototypeOf(
                        notifications[key].data.right, Person.prototype
                    );
                    notifications[key].data.merged = Object.setPrototypeOf(
                        notifications[key].data.merged, Person.prototype
                    );
                    break;
            }
        });
        return notifications;
    }
}