import {ModelBase} from "./ModelBase";
import {Partner} from "./Partner";

export class ConstructionSite extends ModelBase {
    static __typename = 'ConstructionSite';
    id: number = -1;
    name: string;
    active: boolean;
    partner: Partner;

    getID() {
        return this.id;
    }

    toString(): string {
        return this.name;
    }

    getEntityIcon(): string {
        if (this.active) {
            return 'mdi-shovel';
        }
        return 'mdi-shovel-off';

    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: string[] = [];
        tooltips.push('Baustelle');
        if (this.active) {
            tooltips.push('Aktiv');
        } else {
            tooltips.push('Inaktiv');
        }
        return tooltips;

    }
}
