import {Person} from "./models";

class PersonMerged {
    icon: string = 'mdi-account-multiple';
    data: any;

    toString(): string {
        return 'Personen ('
            + this.data.left.toIdentificator()
            + ' und '
            + this.data.right.toIdentificator()
            + ') zusammengeführt: '
            + '<a href="' + this.data.left.getDetailUrl() + '">'
            + this.data.merged.toIdentificator()
            + '</a>';
    }
}

function type(notifications) {
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

function typeOne(notification) {
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

export {PersonMerged, type, typeOne};