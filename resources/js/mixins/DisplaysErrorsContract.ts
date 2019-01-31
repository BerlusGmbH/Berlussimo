export class ErrorMessages {
    public for(index: string): string[] {
        if (this[index]) {
            return (this[index] as string[]);
        } else {
            return []
        }
    }

    [index: string]: string[] | Function;
}

export interface DisplaysErrorsContract {
    errorMessages: ErrorMessages;

    clearErrorMessages(): void;

    extractErrorMessages(error: any): void;
}