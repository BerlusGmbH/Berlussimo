import {ModelBase} from "./ModelBase";


export class Role extends ModelBase {
    static __typename = 'Role';
    icon: string = 'mdi-account';
    id: number = -1;
    name: string = '';

    toString() {
        return this.name;
    }

    getEntityIcon() {
        return 'mdi-account';
    }

    getEntityIconTooltips(): string[] {
        return ['Rolle'];
    }

    getID() {
        return this.id;
    }
}