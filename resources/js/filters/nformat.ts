import Numbro from "../libraries/numbro";

export function nformat(value, format): string {
    if (!format) {
        format = 'decimal';
    }
    switch (format) {
        case 'decimal-4':
            format = '0,0.0000';
            break;
        case 'decimal':
        case 'decimal-2':
            format = '0,0.00';
    }
    value = typeof value != 'number' ? Number(value) : value;
    return Numbro(value).format(format);
}