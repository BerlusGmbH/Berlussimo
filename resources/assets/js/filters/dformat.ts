import moment from 'moment';

export function dformat(value, format): string {
    if (typeof value !== 'string')
        value = String(value);
    if (!format) {
        format = 'date';
    }
    switch (format) {
        case 'date':
            return moment(value).format('DD.MM.YYYY');
        case 'datetime':
            return moment(value).format('DD.MM.YYYY HH:mm:ss');
    }
    return value;
}