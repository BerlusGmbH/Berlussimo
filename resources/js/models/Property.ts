import {ModelBase} from "./ModelBase";
import {Partner} from "./Partner";
import {House} from "./House";
import {Unit} from "./Unit";
import {Assignment} from "./Assignment";
import {Person} from "./Person";
import {Detail} from "./Detail";


export class Property extends ModelBase {
    static __typename = 'Property';
    id: number = -1;
    name: string = '';
    owner: Partner | null = null;
    houses: House[] = [];
    units: Unit[] = [];
    tenants: Person[] = [];
    homeOwners: Person[] = [];
    details: Detail[] = [];
    notes: Detail[] = [];
    assignments: Assignment[] = [];
    livingSpaceSize: number = 0;
    comercialSpaceSize: number = 0;

    toString(): string {
        return this.name;
    }

    hasNotes(): boolean {
        return this.notes && this.notes.length > 0;
    }

    getEntityIcon(): string {
        return 'mdi-city';
    }

    getEntityIconTooltips(): string[] {
        return ['Objekt'];
    }

    getNoteTooltips() {
        return this.notes.map(v => v.value);
    }

    getDetailUrl() {
        return Property.getBaseURL() + '/properties/' + this.id;
    }

    getID() {
        return this.id;
    }
}