import {Active, applyMixins} from "../mixins";
import {Unit} from "./Unit";
import {Person} from "./Person";
import {ModelBase} from "./ModelBase";

export class PurchaseContract extends ModelBase implements Active {
    static __typename = 'PurchaseContract';
    id: number = -1;
    start: string;
    end: string;
    unit: Unit;
    homeOwners: Person[];
    isActive: () => false;

    toString(): string {
        return "KV-" + this.id;
    }

    getID() {
        return this.id;
    }

    getStartDateFieldName() {
        return this.start;
    };

    getEndDateFieldName() {
        return this.end;
    };

    getEntityIcon(): string {
        if (this.isActive()) {
            return 'mdi-checkbox-blank';
        }
        return 'mdi-checkbox-blank-outline';
    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: string[] = [];
        tooltips.push('Kaufvertrag');
        if (this.isActive()) {
            tooltips.push('Aktiv');
        } else {
            tooltips.push('Inaktiv');
        }
        return tooltips;
    }

    getDetailUrl(): string {
        return PurchaseContract.getBaseURL() + '/weg?option=einheit_uebersicht&einheit_id='
            + this.unit.id;
    }

    getApiBaseUrl() {
        return '/api/v1/purchasecontracts'
    }
}

applyMixins(PurchaseContract, [Active]);
