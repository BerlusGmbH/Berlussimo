import {Objekt, Person} from "./models";

export class PersonMerged {
    icon: string = 'mdi-account-multiple';
    data: any;

    static applyPrototype(notification) {
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
}

export class ObjectCopied {
    icon: string = 'mdi-city';
    data: any;

    static applyPrototype(notification) {
        switch (notification.type) {
            case 'App\\Notifications\\ObjectCopied':
                notification = Object.setPrototypeOf(
                    notification, ObjectCopied.prototype
                );
                notification.data.source = Object.setPrototypeOf(
                    notification.data.source, Objekt.prototype
                );
                notification.data.target = Object.setPrototypeOf(
                    notification.data.target, Objekt.prototype
                );
                break;
        }
        return notification;
    }
}