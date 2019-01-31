export abstract class ModelBase {
    __typename: string;

    public static getBaseURL(): string {
        return window.location.origin;
    }

    abstract getID(): number;
}