import axios from "../../libraries/axios";

const base_url = window.location.origin;

export class Person {
    icon: string = 'mdi-account';
    sex: string = '';
    id: number = -1;
    name: string = '';
    first_name: string | null = null;
    birthday: Date;

    toString() {
        let full_name = '';
        if (this.name)
            full_name += this.name;
        if (this.name && this.first_name)
            full_name += ', ';
        if (this.first_name)
            full_name += this.first_name;
        return full_name;
    }

    getEntityIcon() {
        return 'mdi-account';
    }

    getSexIcon() {
        if (this.sex === 'männlich') {
            return 'mdi-gender-male';
        }
        if (this.sex === 'weiblich') {
            return 'mdi-gender-female';
        }
        return '';
    }

    getDetailUrl() {
        return base_url + '/personen/' + this.id;
    }

    getMorphName() {
        return 'PERSON';
    }

    getID() {
        return this.id;
    }

    save() {
        return axios.patch('/api/v1/persons/' + this.id, this);
    }
}

export class Partner {
    PARTNER_NAME: string = '';
    PARTNER_ID: number = -1;
    STRASSE: string = '';
    NUMMER: string = '';
    PLZ: string = '';
    ORT: string = '';
    LAND: string = '';

    toString(): string {
        return this.PARTNER_NAME;
    }

    getEntityIcon(): string {
        return 'mdi-account-multiple';
    }

    getAddress(): string {
        return this.STRASSE + ' ' + this.NUMMER + ', ' + this.PLZ + ' ' + this.ORT + ', ' + this.LAND;
    }

    getDetailUrl() {
        return base_url + '/partner?option=partner_im_detail&partner_id=' + this.PARTNER_ID;
    }
}

export class Objekt {
    OBJEKT_ID: number = -1;
    OBJEKT_KURZNAME: string = '';

    toString(): string {
        return this.OBJEKT_KURZNAME;
    }

    getEntityIcon(): string {
        return 'mdi-city';
    }

    getDetailUrl() {
        return base_url + '/objekte/' + this.OBJEKT_ID;
    }
}

export class Haus {
    HAUS_ID: number = -1;
    HAUS_STRASSE: string = '';
    HAUS_NUMMER: string = '';
    HAUS_PLZ: number;
    HAUS_STADT: string = '';

    icon: string = 'mdi-domain';

    toString(): string {
        return this.HAUS_STRASSE + ' ' + this.HAUS_NUMMER;
    }

    getLocation() {
        return this.HAUS_PLZ + ' ' + this.HAUS_STADT;
    }

    getEntityIcon(): string {
        return 'mdi-domain';
    }

    getDetailUrl(): string {
        return base_url + '/haeuser/' + this.HAUS_ID;
    }
}

export class Einheit {
    EINHEIT_ID: number = -1;
    EINHEIT_KURZNAME: string = '';
    EINHEIT_LAGE: string = '';
    EINHEIT_QM: number = 0;
    HAUS_ID: number = -1;
    TYP: string = '';

    icon: string = 'mdi-cube';

    toString(): string {
        return this.EINHEIT_KURZNAME;
    }

    getEntityIcon(): string {
        return 'mdi-cube';
    }

    getDetailUrl(): string {
        return base_url + '/einheiten/' + this.EINHEIT_ID;
    }
}

export class Detail {
    DETAIL_ID: number = -1;
    DETAIL_NAME: string = '';
    DETAIL_INHALT: string = '';
    DETAIL_BEMERKUNG: string = '';
    DETAIL_ZUORDNUNG_TABELLE: string = '';
    DETAIL_ZUORDNUNG_ID: string = '';

    icon: string = 'mdi-note';

    toString(): string {
        return this.DETAIL_INHALT;
    }

    getDetailUrl(): string {
        return '';
    }

    save() {
        return axios.patch('/api/v1/details/' + this.DETAIL_ID, this);
    }

    create() {
        return axios.post('/api/v1/details', this);
    }

    delete() {
        return axios.delete('/api/v1/details/' + this.DETAIL_ID);
    }
}

export class RentalContract {
    MIETVERTRAG_ID: number = -1;
    MIETVERTRAG_VON: Date;
    MIETVERTRAG_BIS: Date;
    EINHEIT_ID: number = -1;

    toString(): string {
        return "MV-" + this.MIETVERTRAG_ID;
    }

    getEntityIcon(): string {
        return 'mdi-circle';
    }

    getDetailUrl(): string {
        return base_url + '/uebersicht?anzeigen=einheit&einheit_id='
            + this.EINHEIT_ID
            + '&mietvertrag_id='
            + this.MIETVERTRAG_ID;
    }
}

export class PurchaseContract {
    ID: number = -1;
    VON: Date;
    BIS: Date;
    EINHEIT_ID: number = -1;

    toString(): string {
        return "KV-" + this.ID;
    }

    getEntityIcon(): string {
        return 'mdi-checkbox-blank';
    }

    getDetailUrl(): string {
        return base_url + '/weg?option=einheit_uebersicht&einheit_id='
            + this.EINHEIT_ID;
    }
}

export class Job {
    id: number = -1;
    join_date: Date;
    leave_date: Date;
    holidays: number = 0;
    hourly_rate: number = 0;
    hours_per_week: number = 0;
}

export class JobTitle {
    id: number = -1;
    title: string = '';

    getEntityIcon(): string {
        return 'mdi-book-open-variant';
    }
}

export class Bankkonto {
    KONTO_ID: number = -1;
    BEZEICHNUNG: string = '';
    BEGUENSTIGTER: string = '';
    KONTONUMMER: string = '';
    BLZ: string = '';
    IBAN: string = '';
    BIC: string = '';
    INSTITUT: string = '';


    toString(): string {
        return this.BEZEICHNUNG;
    }

    getEntityIcon(): string {
        return 'mdi-currency-eur';
    }

    getAccount(): string {
        return 'IBAN: ' + this.IBAN + ' BIC: ' + this.BIC;
    }

    getDetailUrl() {
        return base_url;
    }
}