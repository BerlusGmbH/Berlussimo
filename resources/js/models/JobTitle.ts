import {ModelBase} from "./ModelBase";

export class JobTitle extends ModelBase {
    static __typename = 'JobTitle';
    id: number = -1;
    title: string = '';

    toString(): string {
        return this.title;
    }

    getEntityIcon(): string {
        return 'mdi-book-open-variant';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Titel'];
    }

    getID() {
        return this.id;
    }
}