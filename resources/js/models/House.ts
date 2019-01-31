import {ModelBase} from "./ModelBase";
import {Assignment} from "./Assignment";
import {Detail} from "./Detail";
import {Unit} from "./Unit";
import {Person} from "./Person";
import {Property} from "./Property";

export class House extends ModelBase {
    static __typename = 'House';
    id: number = -1;
    streetName: string = '';
    streetNumber: string = '';
    postalCode: number;
    city: string = '';
    property: Property;
    tenants: Person[] = [];
    homeOwners: Person[] = [];
    details: Detail[] = [];
    notes: Detail[] = [];
    units: Unit[] = [];
    assignments: Assignment[] = [];
    livingSpaceSize: number = 0;
    commercialSpaceSize: number = 0;
    icon: string = 'mdi-domain';

    toString(): string {
        return this.streetName
            + ' '
            + this.streetNumber;
    }

    hasNotes(): boolean {
        return this.notes && this.notes.length > 0;
    }

    getLocation() {
        return this.postalCode + ' ' + this.city;
    }

    getEntityIcon(): string {
        return 'mdi-domain';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Haus'];
    }

    getNoteTooltips() {
        return this.notes.map(v => v.value);
    }

    getID(): number {
        return this.id;
    }

    getDetailUrl(): string {
        return House.getBaseURL() + '/houses/' + this.id;
    }
}
