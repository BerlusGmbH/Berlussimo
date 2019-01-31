import {Active, applyMixins} from "../mixins";
import {Unit} from "./Unit";
import {Person} from "./Person";
import {ModelBase} from "./ModelBase";


export class RentalContract extends ModelBase implements Active {
    static __typename = 'RentalContract';
    id: number = -1;
    start: string;
    end: string;
    unit: Unit;
    tenants: Person[];
    isActive: () => boolean;

    getStartDateFieldName() {
        return this.start;
    };

    getEndDateFieldName() {
        return this.end;
    };

    toString(): string {
        return "MV-" + this.id;
    }

    getEntityIcon(): string {
        if (this.isActive()) {
            return 'mdi-circle';
        }
        return 'mdi-circle-outline';
    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: string[] = [];
        tooltips.push('Mietvertrag');
        if (this.isActive()) {
            tooltips.push('Aktiv');
        } else {
            tooltips.push('Inaktiv');
        }
        return tooltips;
    }

    getDetailUrl(): string {
        let url = RentalContract.getBaseURL()
            + '/uebersicht?anzeigen=einheit'
            + '&mietvertrag_id=' + this.id;
        url += this.unit ? '&einheit_id=' + this.unit.id : '';
        return url;
    }

    getMorphName() {
        return 'Mietvertrag';
    }

    getApiBaseUrl() {
        return '/api/v1/rentalcontracts'
    }

    getID() {
        return this.id;
    }
}

applyMixins(RentalContract, [Active]);
