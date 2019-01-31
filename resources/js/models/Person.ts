import {ModelBase} from "./ModelBase";
import {RentalContract} from "./RentalContract";
import {PurchaseContract} from "./PurchaseContract";
import {Job} from "./Job";
import {Detail} from "./Detail";


export class Person extends ModelBase {
    static __typename = 'Person';
    icon: string = 'mdi-account';
    gender: string = '';
    id: number = -1;
    lastName: string = '';
    firstName: string | null = null;
    name: string = '';
    fullName: string = '';
    addressName: string = '';
    birthday: Date;
    rentalContracts: RentalContract[];
    purchaseContracts: PurchaseContract[];
    jobs: Job[];
    audits: Object[];
    notes: Detail[] = [];

    toString() {
        return this.name;
    }

    getEntityIcon() {
        return 'mdi-account';
    }

    getEntityIconTooltips(): string[] {
        return ['Person'];
    }

    getSexIcon() {
        if (this.gender === 'MALE') {
            return 'mdi-gender-male';
        }
        if (this.gender === 'FEMALE') {
            return 'mdi-gender-female';
        }
        return '';
    }

    getNoteTooltips() {
        return this.notes.map(v => v.value);
    }

    getDetailUrl() {
        return Person.getBaseURL() + '/persons/' + this.id;
    }

    getID() {
        return this.id;
    }

    hasNotes(): boolean {
        return this.notes && this.notes.length > 0;
    }
}