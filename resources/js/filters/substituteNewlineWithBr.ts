export function substituteNewlineWithBr(value): string {
    value = value.replace(/\r\n/g, '<br>');
    value = value.replace(/\r/g, '<br>');
    return value.replace(/\n/g, '<br>');
}