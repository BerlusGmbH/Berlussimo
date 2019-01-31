import {ModelBase} from "./ModelBase";
import {Unit} from "./Unit";

export class AccountingEntity extends ModelBase {
    static __typename = 'AccountingEntity';
    id: number = -1;
    name: string;
    units: Unit[];

    getID() {
        return this.id;
    }

    toString(): string {
        return this.name;
    }

    getEntityIcon(): string {
        return 'mdi-hexagon-multiple';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Wirtschaftseinheit'];
    }
}