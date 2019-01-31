import {Assignment} from "./Assignment";
import {RentalContract} from "./RentalContract";
import {House} from "./House";
import {Person} from "./Person";
import {Detail} from "./Detail";
import {PurchaseContract} from "./PurchaseContract";
import {ModelBase} from "./ModelBase";


export class Unit extends ModelBase {
    public static readonly LIVING_SPACE = "LIVING_SPACE";
    public static readonly COMMERCIAL_SPACE = "COMMERCIAL_SPACE";
    public static readonly HOUSE_OWNER_LIVING_SPACE = "HOUSE_OWNER_LIVING_SPACE";
    public static readonly PARKING_SPACE = "PARKING_SPACE";
    public static readonly GARAGE = "GARAGE";
    public static readonly CELLAR = "CELLAR";
    public static readonly OPEN_SPACE = "OPEN_SPACE";
    public static readonly ADVERTISING_SPACE = "ADVERTISING_SPACE";
    public static readonly BUGGY_SPACE = "BUGGY_SPACE";
    public static readonly ROOM = "ROOM";
    static __typename = 'Unit';
    id: number = -1;
    name: string = '';
    location: string = '';
    size: number = 0;
    type: string = '';
    details: Detail[];
    notes: Detail[];
    house: House;
    unitType: string = '';
    purchaseContracts: RentalContract[];
    rentalContracts: PurchaseContract[];
    tenants: Person[];
    homeOwners: Person[];
    assignments: Assignment[];
    rented: boolean = false;
    icon: string = 'mdi-cube';

    toString(): string {
        return this.name;
    }

    hasNotes(): boolean {
        return this.notes && this.notes.length > 0;
    }

    getEntityIcon(): string {
        if (this.rented) {
            return 'mdi-cube';
        }
        return 'mdi-cube-outline';
    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: string[] = [];
        tooltips.push('Einheit');
        if (this.rented) {
            tooltips.push('Vermietet')
        } else {
            tooltips.push('Unvermietet')
        }
        return tooltips;
    }

    getNoteTooltips() {
        return this.notes.map(v => v.value);
    }

    getKindIcon() {
        switch (this.unitType) {
            case Unit.LIVING_SPACE:
                return 'mdi-home';
            case Unit.COMMERCIAL_SPACE:
                return 'mdi-store';
            case Unit.PARKING_SPACE:
                return 'mdi-car';
            case Unit.GARAGE:
                return 'mdi-garage';
            case Unit.CELLAR:
                return 'mdi-ghost';
            case Unit.OPEN_SPACE:
                return 'mdi-nature-people';
            case Unit.HOUSE_OWNER_LIVING_SPACE:
                return 'mdi-home-variant';
            case Unit.ADVERTISING_SPACE:
                return 'mdi-newspaper';
            default:
                return 'mdi-home';
        }
    }

    getKindTooltips() {
        switch (this.unitType) {
            case Unit.OPEN_SPACE:
                return ['Freifläche'];
            case Unit.ADVERTISING_SPACE:
                return ['Werbefläche'];
            case Unit.HOUSE_OWNER_LIVING_SPACE:
                return ['Wohneigentum'];
            case Unit.LIVING_SPACE:
                return ['Wohnfläche'];
            case Unit.COMMERCIAL_SPACE:
                return ['Gewerbefläche'];
            case Unit.CELLAR:
                return ['Keller'];
            case Unit.GARAGE:
                return ['Garage'];
            case Unit.PARKING_SPACE:
                return ['Parkplatz'];
            case Unit.BUGGY_SPACE:
                return ['Kinderwagenstellplatz'];
            case Unit.ROOM:
                return ['Zimmer (möbliert)'];
            default:
                return [this.unitType];
        }
    }

    getDetailUrl(): string {
        return Unit.getBaseURL() + '/units/' + this.getID();
    }

    getID(): number {
        return this.id;
    }
}