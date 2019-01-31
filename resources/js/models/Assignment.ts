import {ModelBase} from "./ModelBase";
import {Partner} from "./Partner";
import {Person} from "./Person";

export class Assignment extends ModelBase {
    static __typename: string = 'Assignment';
    id: number;
    description: string | null = null;
    createdAt: string | null = null;
    highPriority: boolean = false;
    done: boolean = false;
    author: Person | null = null;
    assignedTo: Person | Partner | null = null;
    costBearer: any = null;

    getEntityIcon(): string {
        if (this.done) {
            return 'mdi-clipboard-check';
        } else if (this.highPriority) {
            return 'mdi-clipboard-alert';
        }
        return 'mdi-clipboard';
    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: Array<string> = [];
        tooltips.push('Auftrag');
        if (this.done) {
            tooltips.push('Erledigt');
        } else {
            tooltips.push('Offen');
        }
        if (this.highPriority) {
            tooltips.push('Akut');
        }
        return tooltips;
    }

    getID() {
        return this.id;
    }

    toString() {
        return 'A-' + this.id;
    }
}
