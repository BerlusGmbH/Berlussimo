import axios from "../../libraries/axios";
import applyMixins from "./mixins";
import Active from "./active";

const base_url = window.location.origin;

export abstract class Model {

    abstract getID(): number;

    type: string;

    static applyPrototype(model) {
        switch (model.type) {
            case Assignment.type:
                return Assignment.applyPrototype(model);
            case Bankkonto.type:
                return Bankkonto.applyPrototype(model);
            case Detail.type:
                return Detail.applyPrototype(model);
            case Einheit.type:
                return Einheit.applyPrototype(model);
            case Haus.type:
                return Haus.applyPrototype(model);
            case Job.type:
                return Job.applyPrototype(model);
            case JobTitle.type:
                return JobTitle.applyPrototype(model);
            case RentalContract.type:
                return RentalContract.applyPrototype(model);
            case PurchaseContract.type:
                return PurchaseContract.applyPrototype(model);
            case Objekt.type:
                return Objekt.applyPrototype(model);
            case Partner.type:
                return Partner.applyPrototype(model);
            case Person.type:
                return Person.applyPrototype(model);
            case AccountingEntity.type:
                return AccountingEntity.applyPrototype(model);
            case ConstructionSite.type:
                return ConstructionSite.applyPrototype(model);
            case InvoiceItem.type:
                return InvoiceItem.applyPrototype(model);
            case BankAccountStandardChart.type:
                return BankAccountStandardChart.applyPrototype(model);
            case BookingAccount.type:
                return BookingAccount.applyPrototype(model);
            case Invoice.type:
                return Invoice.applyPrototype(model);
        }
    }
}

export class Assignment extends Model {
    static type: string = 'assignment';
    T_ID: number;
    TEXT: string | null = null;
    ERSTELLT: string | null = null;
    AKUT: string = 'NEIN';
    ERLEDIGT: string = '0';
    VERFASSER_ID: number;
    BENUTZER_TYP: string;
    BENUTZER_ID: number;
    KOS_TYP: string;
    KOS_ID: number;
    von: Person | null = null;
    an: Person | Partner | null = null;
    kostentraeger: any = null;

    static applyPrototype(assignment) {
        Object.setPrototypeOf(assignment, Assignment.prototype);
        if (assignment.von) {
            Model.applyPrototype(assignment.von);
        }
        if (assignment.an) {
            Model.applyPrototype(assignment.an);
        }
        if (assignment.kostentraeger) {
            Model.applyPrototype(assignment.kostentraeger);
        }
        return assignment;
    }

    getEntityIcon(): string {
        if (this.ERLEDIGT === '1') {
            return 'mdi-clipboard-check';
        } else if (this.AKUT === 'JA') {
            return 'mdi-clipboard-alert';
        }
        return 'mdi-clipboard';
    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: Array<string> = [];
        tooltips.push('Auftrag');
        if (this.ERLEDIGT === '1') {
            tooltips.push('Erledigt');
        } else {
            tooltips.push('Offen');
        }
        if (this.AKUT === 'JA') {
            tooltips.push('Akut');
        }
        return tooltips;
    }

    getID() {
        return this.T_ID;
    }

    getApiBaseUrl() {
        return base_url + '/api/v1/assignments'
    }

    save() {
        return axios.put(this.getApiBaseUrl() + '/' + this.T_ID, this);
    }

    create() {
        return axios.post(this.getApiBaseUrl(), this);
    }

    toString() {
        return 'A-' + this.T_ID;
    }
}

export class Person extends Model {
    icon: string = 'mdi-account';
    sex: string = '';
    id: number = -1;
    name: string = '';
    first_name: string | null = null;
    birthday: Date;
    mietvertraege: Array<RentalContract>;
    kaufvertraege: Array<PurchaseContract>;
    jobs_as_employee: Array<Job>;
    audits: Array<Object>;
    hinweise: Array<Detail> = [];

    static type = 'person';

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

    getEntityIconTooltips(): Array<string> {
        return ['Person'];
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

    getNoteTooltips() {
        return this.hinweise.map(v => v.DETAIL_INHALT);
    }

    getDetailUrl() {
        return base_url + '/persons/' + this.id;
    }

    getApiBaseUrl() {
        return base_url + '/api/v1/persons';
    }

    getMorphName() {
        return 'Person';
    }

    getID() {
        return this.id;
    }

    save() {
        return axios.patch(this.getApiBaseUrl() + '/' + this.id, this);
    }

    create() {
        return axios.post(this.getApiBaseUrl(), this);
    }

    hasNotes(): boolean {
        return this.hinweise && this.hinweise.length > 0;
    }

    static applyPrototype(person: Person): Person {
        Object.setPrototypeOf(person, Person.prototype);
        ['common_details', 'hinweise', 'adressen', 'emails', 'faxs', 'phones'].forEach((details) => {
            if (person[details]) {
                person[details].forEach((detail) => {
                    Detail.applyPrototype(detail);
                });
            }
        });
        if (person.audits) {
            Array.prototype.forEach.call(person.audits, (audit) => {
                if (audit.user) {
                    Object.setPrototypeOf(audit.user, Person.prototype);
                }
            });
        }
        if (person.mietvertraege) {
            Array.prototype.forEach.call(person.mietvertraege, (mietvertrag) => {
                RentalContract.applyPrototype(mietvertrag);
            });
        }
        if (person.kaufvertraege) {
            Array.prototype.forEach.call(person.kaufvertraege, (kaufvertrag) => {
                PurchaseContract.applyPrototype(kaufvertrag);
            });
        }
        if (person.jobs_as_employee) {
            Array.prototype.forEach.call(person.jobs_as_employee, (job) => {
                Job.applyPrototype(job);
            });
        }
        return person;
    }
}

export class Partner extends Model {
    PARTNER_NAME: string = '';
    PARTNER_ID: number = -1;
    STRASSE: string = '';
    NUMMER: string = '';
    PLZ: string = '';
    ORT: string = '';
    LAND: string = '';

    static type = 'partner';

    toString(): string {
        return this.PARTNER_NAME;
    }

    getEntityIcon(): string {
        return 'mdi-account-multiple';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Partner'];
    }

    getAddress(): string {
        return this.STRASSE + ' ' + this.NUMMER + ', ' + this.PLZ + ' ' + this.ORT + ', ' + this.LAND;
    }

    getDetailUrl() {
        return base_url + '/partner?option=partner_im_detail&partner_id=' + this.PARTNER_ID;
    }

    getMorphName() {
        return 'Partner';
    }

    getID() {
        return this.PARTNER_ID;
    }

    static applyPrototype(partner: Partner) {
        Object.setPrototypeOf(partner, Partner.prototype);
        return partner;
    }
}

export class Objekt extends Model {
    OBJEKT_ID: number = -1;
    OBJEKT_KURZNAME: string = '';
    EIGENTUEMER_PARTNER: number;

    haeuser: Array<Haus> = [];
    einheiten: Array<Einheit> = [];
    eigentuemer: Partner | null = null;
    mieter: Array<Person> = [];
    weg_eigentuemer: Array<Person> = [];
    common_details: Array<Detail> = [];
    hinweise: Array<Detail> = [];
    auftraege: Array<Assignment> = [];
    wohnflaeche: number = 0;
    gewerbeflaeche: number = 0;

    static type = 'pm_object';

    toString(): string {
        return this.OBJEKT_KURZNAME;
    }

    hasNotes(): boolean {
        return this.hinweise && this.hinweise.length > 0;
    }

    getEntityIcon(): string {
        return 'mdi-city';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Objekt'];
    }

    getNoteTooltips() {
        return this.hinweise.map(v => v.DETAIL_INHALT);
    }

    getDetailUrl() {
        return base_url + '/objects/' + this.OBJEKT_ID;
    }

    getApiBaseUrl() {
        return base_url + '/api/v1/objects'
    }

    getMorphName() {
        return 'Objekt';
    }

    getID() {
        return this.OBJEKT_ID;
    }

    save() {
        return axios.patch(this.getApiBaseUrl() + '/' + this.OBJEKT_ID, this);
    }

    create() {
        return axios.post(this.getApiBaseUrl(), this);
    }

    static applyPrototype(objekt: Objekt) {
        Object.setPrototypeOf(objekt, Objekt.prototype);
        if (objekt.haeuser) {
            Array.prototype.forEach.call(objekt.haeuser, (haus) => {
                Haus.applyPrototype(haus);
            });
        }
        if (objekt.common_details) {
            Array.prototype.forEach.call(objekt.common_details, (detail) => {
                Detail.applyPrototype(detail);
            });
        }
        if (objekt.hinweise) {
            Array.prototype.forEach.call(objekt.hinweise, (detail) => {
                Detail.applyPrototype(detail);
            });
        }
        if (objekt.einheiten) {
            Array.prototype.forEach.call(objekt.einheiten, (einheit) => {
                Einheit.applyPrototype(einheit);
            });
        }
        if (objekt.eigentuemer) {
            Partner.applyPrototype(objekt.eigentuemer);
        }
        if (objekt.mieter) {
            Array.prototype.forEach.call(objekt.mieter, (person) => {
                Person.applyPrototype(person);
            });
        }
        if (objekt.weg_eigentuemer) {
            Array.prototype.forEach.call(objekt.weg_eigentuemer, (person) => {
                Person.applyPrototype(person);
            });
        }
        if (objekt.auftraege) {
            Array.prototype.forEach.call(objekt.auftraege, (assignment) => {
                Assignment.applyPrototype(assignment);
            });
        }
        return objekt;
    }
}

export class Haus extends Model {
    HAUS_ID: number = -1;
    HAUS_STRASSE: string = '';
    HAUS_NUMMER: string = '';
    HAUS_PLZ: number;
    HAUS_STADT: string = '';
    OBJEKT_ID: number;

    objekt: Objekt;
    mieter: Array<Person> = [];
    weg_eigentuemer: Array<Person> = [];
    common_details: Array<Detail> = [];
    hinweise: Array<Detail> = [];
    einheiten: Array<Einheit> = [];
    auftraege: Array<Assignment> = [];
    wohnflaeche: number = 0;
    gewerbeflaeche: number = 0;

    static type = 'house';

    icon: string = 'mdi-domain';

    toString(): string {
        return this.HAUS_STRASSE
            + ' '
            + this.HAUS_NUMMER;
    }

    hasNotes(): boolean {
        return this.hinweise && this.hinweise.length > 0;
    }

    getLocation() {
        return this.HAUS_PLZ + ' ' + this.HAUS_STADT;
    }

    getEntityIcon(): string {
        return 'mdi-domain';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Haus'];
    }

    getNoteTooltips() {
        return this.hinweise.map(v => v.DETAIL_INHALT);
    }

    getApiBaseUrl() {
        return base_url + '/api/v1/houses'
    }

    getMorphName() {
        return 'Haus';
    }

    getID(): number {
        return this.HAUS_ID;
    }

    getDetailUrl(): string {
        return base_url + '/houses/' + this.HAUS_ID;
    }

    save() {
        return axios.patch(this.getApiBaseUrl() + '/' + this.HAUS_ID, this);
    }

    create() {
        return axios.post(this.getApiBaseUrl(), this);
    }

    static applyPrototype(haus: Haus) {
        Object.setPrototypeOf(haus, Haus.prototype);
        if (haus.objekt) {
            Objekt.applyPrototype(haus.objekt);
        }
        if (haus.common_details) {
            Array.prototype.forEach.call(haus.common_details, (detail) => {
                Detail.applyPrototype(detail);
            });
        }
        if (haus.hinweise) {
            Array.prototype.forEach.call(haus.hinweise, (detail) => {
                Detail.applyPrototype(detail);
            });
        }
        if (haus.mieter) {
            Array.prototype.forEach.call(haus.mieter, (person) => {
                Person.applyPrototype(person);
            });
        }
        if (haus.weg_eigentuemer) {
            Array.prototype.forEach.call(haus.weg_eigentuemer, (person) => {
                Person.applyPrototype(person);
            });
        }
        if (haus.einheiten) {
            Array.prototype.forEach.call(haus.einheiten, (einheit) => {
                Einheit.applyPrototype(einheit);
            });
        }
        if (haus.auftraege) {
            Array.prototype.forEach.call(haus.auftraege, (assignment) => {
                Assignment.applyPrototype(assignment);
            });
        }
        return haus;
    }
}

export class Einheit extends Model {
    EINHEIT_ID: number = -1;
    EINHEIT_KURZNAME: string = '';
    EINHEIT_LAGE: string = '';
    EINHEIT_QM: number = 0;
    HAUS_ID: number = -1;
    TYP: string = '';

    common_details: Array<Detail>;
    hinweise: Array<Detail>;
    haus: Haus;
    einheit: Einheit;
    kaufvertraege: Array<RentalContract>;
    mietvertraege: Array<PurchaseContract>;
    mieter: Array<Person>;
    weg_eigentuemer: Array<Person>;
    auftraege: Array<Assignment>;
    vermietet: boolean = false;

    static type = 'unit';

    icon: string = 'mdi-cube';

    toString(): string {
        return this.EINHEIT_KURZNAME;
    }

    hasNotes(): boolean {
        return this.hinweise && this.hinweise.length > 0;
    }

    getEntityIcon(): string {
        if (this.vermietet) {
            return 'mdi-cube';
        }
        return 'mdi-cube-outline';
    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: Array<string> = [];
        tooltips.push('Einheit');
        if (this.vermietet) {
            tooltips.push('Vermietet')
        } else {
            tooltips.push('Unvermietet')
        }
        return tooltips;
    }

    getNoteTooltips() {
        return this.hinweise.map(v => v.DETAIL_INHALT);
    }

    getKindIcon() {
        switch (this.TYP) {
            case 'Wohnraum':
                return 'mdi-home';
            case 'Gewerbe':
                return 'mdi-store';
            case 'Stellplatz':
                return 'mdi-car';
            case 'Garage':
                return 'mdi-garage';
            case 'Keller':
                return 'mdi-ghost';
            case 'Freiflaeche':
                return 'mdi-nature-people';
            case 'Wohneigentum':
                return 'mdi-home-variant';
            case 'Werbeflaeche':
                return 'mdi-newspaper';
            default:
                return 'mdi-home';
        }
    }

    getKindTooltips() {
        switch (this.TYP) {
            case 'Freiflaeche':
                return ['Freifläche'];
            case 'Werbeflaeche':
                return ['Werbefläche'];
            default:
                return [this.TYP];
        }
    }

    getDetailUrl(): string {
        return base_url + '/units/' + this.EINHEIT_ID;
    }

    getApiBaseUrl() {
        return base_url + '/api/v1/units'
    }

    getMorphName() {
        return 'Einheit';
    }

    getID(): number {
        return this.EINHEIT_ID;
    }

    save() {
        return axios.patch('/api/v1/units/' + this.EINHEIT_ID, this);
    }

    create() {
        return axios.post('/api/v1/units', this);
    }

    static applyPrototype(einheit: Einheit) {
        Object.setPrototypeOf(einheit, Einheit.prototype);
        if (einheit.haus) {
            Haus.applyPrototype(einheit.haus);
        }
        if (einheit.common_details) {
            Array.prototype.forEach.call(einheit.common_details, (detail) => {
                Detail.applyPrototype(detail);
            });
        }
        if (einheit.hinweise) {
            Array.prototype.forEach.call(einheit.hinweise, (detail) => {
                Detail.applyPrototype(detail);
            });
        }
        if (einheit.kaufvertraege) {
            Array.prototype.forEach.call(einheit.kaufvertraege, (contract) => {
                PurchaseContract.applyPrototype(contract);
            });
        }
        if (einheit.mietvertraege) {
            Array.prototype.forEach.call(einheit.mietvertraege, (contract) => {
                RentalContract.applyPrototype(contract);
            });
        }
        if (einheit.mieter) {
            Array.prototype.forEach.call(einheit.mieter, (person) => {
                Person.applyPrototype(person);
            });
        }
        if (einheit.weg_eigentuemer) {
            Array.prototype.forEach.call(einheit.weg_eigentuemer, (person) => {
                Person.applyPrototype(person);
            });
        }
        if (einheit.auftraege) {
            Array.prototype.forEach.call(einheit.auftraege, (assignment) => {
                Assignment.applyPrototype(assignment);
            });
        }
        return einheit;
    }
}

export class Detail extends Model {
    DETAIL_ID: number = -1;
    DETAIL_NAME: string = '';
    DETAIL_INHALT: string = '';
    DETAIL_BEMERKUNG: string = '';
    DETAIL_ZUORDNUNG_TABELLE: string = '';
    DETAIL_ZUORDNUNG_ID: string = '';

    static type = 'detail';

    icon: string = 'mdi-note';

    toString(): string {
        return this.DETAIL_INHALT;
    }

    getDetailUrl(): string {
        return '';
    }

    getID() {
        return this.DETAIL_ID;
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

    static applyPrototype(detail: Detail) {
        Object.setPrototypeOf(detail, Detail.prototype);
        return detail;
    }
}

export class RentalContract extends Model implements Active {
    MIETVERTRAG_ID: number = -1;
    MIETVERTRAG_VON: string;
    MIETVERTRAG_BIS: string;
    EINHEIT_ID: number = -1;

    einheit: Einheit;
    mieter: Array<Person>;

    static type = 'rental_contract';

    getStartDateFieldName() {
        return this.MIETVERTRAG_VON;
    };

    getEndDateFieldName() {
        return this.MIETVERTRAG_BIS;
    };

    isActive: () => false;

    toString(): string {
        return "MV-" + this.MIETVERTRAG_ID;
    }

    getEntityIcon(): string {
        if (this.isActive()) {
            return 'mdi-circle';
        }
        return 'mdi-circle-outline';
    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: Array<string> = [];
        tooltips.push('Mietvertrag');
        if (this.isActive()) {
            tooltips.push('Aktiv');
        } else {
            tooltips.push('Inaktiv');
        }
        return tooltips;
    }

    getDetailUrl(): string {
        return base_url + '/uebersicht?anzeigen=einheit&einheit_id='
            + this.EINHEIT_ID
            + '&mietvertrag_id='
            + this.MIETVERTRAG_ID;
    }

    getMorphName() {
        return 'Mietvertrag';
    }

    getApiBaseUrl() {
        return base_url + '/api/v1/rentalcontracts'
    }

    getID() {
        return this.MIETVERTRAG_ID;
    }

    static applyPrototype(contract: RentalContract) {
        Object.setPrototypeOf(contract, RentalContract.prototype);
        if (contract.einheit) {
            Einheit.applyPrototype(contract.einheit);
        }
        if (contract.mieter) {
            Array.prototype.forEach.call(contract.mieter, (person) => {
                Person.applyPrototype(person);
            });

        }
        return contract;
    }
}

applyMixins(RentalContract, [Active]);

export class PurchaseContract extends Model implements Active {
    ID: number = -1;
    VON: string;
    BIS: string;
    EINHEIT_ID: number = -1;

    einheit: Einheit;
    eigentuemer: Array<Person>;

    isActive: () => false;

    static type = 'purchase_contract';

    toString(): string {
        return "KV-" + this.ID;
    }

    getMorphName() {
        return 'Eigentuemer';
    }

    getID() {
        return this.ID;
    }

    getStartDateFieldName() {
        return this.VON;
    };

    getEndDateFieldName() {
        return this.BIS;
    };

    getEntityIcon(): string {
        if (this.isActive()) {
            return 'mdi-checkbox-blank';
        }
        return 'mdi-checkbox-blank-outline';
    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: Array<string> = [];
        tooltips.push('Kaufvertrag');
        if (this.isActive()) {
            tooltips.push('Aktiv');
        } else {
            tooltips.push('Inaktiv');
        }
        return tooltips;
    }

    getDetailUrl(): string {
        return base_url + '/weg?option=einheit_uebersicht&einheit_id='
            + this.EINHEIT_ID;
    }

    getApiBaseUrl() {
        return base_url + '/api/v1/purchasecontracts'
    }

    static applyPrototype(contract: PurchaseContract) {
        Object.setPrototypeOf(contract, PurchaseContract.prototype);
        if (contract.einheit) {
            Einheit.applyPrototype(contract.einheit);
        }
        if (contract.eigentuemer) {
            Array.prototype.forEach.call(contract.eigentuemer, (person) => {
                Person.applyPrototype(person);
            });
        }
        return contract;
    }
}

applyMixins(PurchaseContract, [Active]);

export class Job extends Model {
    id: number = -1;
    employer_id: number = -1;
    employee_id: number = -1;
    join_date: Date;
    leave_date: Date;
    holidays: number = 0;
    hourly_rate: number = 0;
    hours_per_week: number = 0;
    employer: any;
    employee: any;
    title: any;

    static type = 'job';

    getID() {
        return this.id;
    }

    static applyPrototype(job: Job) {
        Object.setPrototypeOf(job, Job.prototype);
        if (job.employer) {
            Object.setPrototypeOf(job.employer, Partner.prototype);
        }
        if (job.employee) {
            Object.setPrototypeOf(job.employee, Person.prototype);
        }
        if (job.title) {
            Object.setPrototypeOf(job.title, JobTitle.prototype);
        }
        return job;
    }
}

export class JobTitle extends Model {
    id: number = -1;
    title: string = '';

    static type = 'job_title';

    toString(): string {
        return this.title;
    }

    getEntityIcon(): string {
        return 'mdi-book-open-variant';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Titel'];
    }

    getID() {
        return this.id;
    }

    static applyPrototype(jobTitle: JobTitle) {
        Object.setPrototypeOf(jobTitle, JobTitle.prototype);
        return jobTitle;
    }
}

export class Bankkonto extends Model {
    KONTO_ID: number = -1;
    BEZEICHNUNG: string = '';
    BEGUENSTIGTER: string = '';
    KONTONUMMER: string = '';
    BLZ: string = '';
    IBAN: string = '';
    BIC: string = '';
    INSTITUT: string = '';

    static type = 'bank_account';

    getID() {
        return this.KONTO_ID;
    }

    toString(): string {
        return this.BEZEICHNUNG;
    }

    getEntityIcon(): string {
        return 'mdi-currency-eur';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Bankkonto'];
    }

    getAccount(): string {
        return 'IBAN: ' + this.IBAN + ' BIC: ' + this.BIC;
    }

    getDetailUrl() {
        return base_url;
    }

    static applyPrototype(bankaccount: Bankkonto) {
        Object.setPrototypeOf(bankaccount, Bankkonto.prototype);
        return bankaccount;
    }
}

export class AccountingEntity extends Model {
    W_ID: number = -1;
    W_NAME: string;

    einheiten: Array<Einheit>;

    static type = 'accounting_entity';

    getID() {
        return this.W_ID;
    }

    getMorphName() {
        return 'Wirtschaftseinheit';
    }

    toString(): string {
        return this.W_NAME;
    }

    getEntityIcon(): string {
        return 'mdi-hexagon-multiple';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Wirtschaftseinheit'];
    }

    getDetailUrl() {
        return base_url;
    }

    static applyPrototype(accounting_entity: AccountingEntity) {
        Object.setPrototypeOf(accounting_entity, AccountingEntity.prototype);
        if (accounting_entity.einheiten) {
            Array.prototype.forEach.call(accounting_entity.einheiten, (unit) => {
                Einheit.applyPrototype(unit);
            });
        }
        return accounting_entity;
    }
}

export class ConstructionSite extends Model {
    ID: number = -1;
    BEZ: string;
    PARTNER_ID: number;
    AKTIV: string;

    partner: Partner;

    static type = 'construction_site';

    getID() {
        return this.ID;
    }

    getMorphName() {
        return 'Baustelle_ext';
    }

    toString(): string {
        return this.BEZ;
    }

    getEntityIcon(): string {
        if (this.AKTIV === '1') {
            return 'mdi-shovel';
        }
        return 'mdi-shovel-off';

    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: Array<string> = [];
        tooltips.push('Baustelle');
        if (this.AKTIV === '1') {
            tooltips.push('Aktiv');
        } else {
            tooltips.push('Inaktiv');
        }
        return tooltips;

    }

    getDetailUrl() {
        return base_url;
    }

    static applyPrototype(construction_site: ConstructionSite) {
        Object.setPrototypeOf(construction_site, ConstructionSite.prototype);
        if (construction_site.partner) {
            Partner.applyPrototype(construction_site.partner);
        }
        return construction_site;
    }
}

export class Invoice extends Model {
    static type = 'invoice';
    BELEG_NR: number = -1;
    RECHNUNGSNUMMER: string;
    AUSTELLER_AUSGANGS_RNR: number;
    EMPFAENGER_EINGANGS_RNR: number;
    RECHNUNGSTYP: string;
    RECHNUNGSDATUM: string;
    EINGANGSDATUM: string;
    FAELLIG_AM: string;
    BEZAHLT_AM: string;
    NETTO: number;
    BRUTTO: number;
    SKONTOBETRAG: number;
    KURZBESCHREIBUNG: string;
    advance_payment_invoice_id: number | null;
    from: Partner;
    to: Partner;
    bank_account: Bankkonto;
    lines: Array<InvoiceLine>;
    advance_payment_invoice: Invoice;
    advance_payment_invoices: Array<Invoice>;
    servicetime_from: string;
    servicetime_to: string;

    static applyPrototype(invoice: Invoice) {
        Object.setPrototypeOf(invoice, Invoice.prototype);
        if (invoice.from) {
            Partner.applyPrototype(invoice.from);
        }
        if (invoice.to) {
            Model.applyPrototype(invoice.to);
        }
        if (invoice.bank_account) {
            Bankkonto.applyPrototype(invoice.bank_account);
        }
        if (invoice.advance_payment_invoice) {
            Invoice.applyPrototype(invoice.advance_payment_invoice);
        }
        if (invoice.advance_payment_invoices) {
            invoice.advance_payment_invoices.forEach((v) => {
                Invoice.applyPrototype(v);
            })
        }
        if (invoice.lines) {
            invoice.lines.forEach((v) => {
                InvoiceLine.applyPrototype(v);
            })
        }
        return invoice;
    }

    save() {
        if (this.RECHNUNGSTYP !== 'Teilrechnung' && this.RECHNUNGSTYP !== 'Schlussrechnung') {
            this.advance_payment_invoice_id = null;
        }
        return axios.put('/api/v1/invoices/' + this.BELEG_NR, this);
    }

    getID() {
        return this.BELEG_NR;
    }

    toString(): string {
        return this.RECHNUNGSNUMMER;
    }

    getEntityIcon(): string {
        return 'mdi-receipt';

    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: Array<string> = [];
        tooltips.push(this.RECHNUNGSTYP);
        return tooltips;
    }

    getDetailUrl() {
        return base_url + '/invoices/' + this.BELEG_NR;
    }

    isAdvancePaymentInvoice() {
        return ['Schlussrechnung', 'Teilrechnung'].includes(this.RECHNUNGSTYP);
    }
}

export class InvoiceLine extends Model {
    static type = 'invoice_line';
    RECHNUNGEN_POS_ID: number = -1;
    POSITION: number;
    BELEG_NR: number;
    U_BELEG_NR: number;
    ART_LIEFERANT: number;
    ARTIKEL_NR: string;
    MENGE: number = 0.00;
    PREIS: string = "0.0000";
    MWST_SATZ: number = 19;
    RABATT_SATZ: string = "0.00";
    SKONTO: string = "0.00";
    GESAMT_NETTO: number;
    EINHEIT: string = "Stk";
    BEZEICHNUNG: string;
    assignments: Array<InvoiceLineAssignment>;

    static applyPrototype(invoiceLine: InvoiceLine) {
        Object.setPrototypeOf(invoiceLine, InvoiceLine.prototype);
        if (invoiceLine.assignments) {
            invoiceLine.assignments.forEach(v => {
                InvoiceLineAssignment.applyPrototype(v);
            });
        }
        return invoiceLine;
    }

    getID() {
        return this.RECHNUNGEN_POS_ID;
    }

    toString(): string {
        return this.GESAMT_NETTO + ' €';
    }

    getEntityIcon(): string {
        return 'mdi-receipt';

    }

    fill(item: InvoiceItem) {
        this.ART_LIEFERANT = Number(item.ART_LIEFERANT);
        this.ARTIKEL_NR = item.ARTIKEL_NR;
        this.PREIS = item.LISTENPREIS;
        this.MWST_SATZ = item.MWST_SATZ;
        this.SKONTO = String(item.SKONTO);
        this.RABATT_SATZ = String(item.RABATT_SATZ);
        this.EINHEIT = item.EINHEIT;
        this.BEZEICHNUNG = item.BEZEICHNUNG
    }

    create() {
        return axios.post(base_url + '/api/v1/invoice-lines', this);
    }

    save() {
        return axios.put(base_url + '/api/v1/invoice-lines/' + this.RECHNUNGEN_POS_ID, this);
    }

    delete() {
        return axios.delete(base_url + '/api/v1/invoice-lines/' + this.RECHNUNGEN_POS_ID);
    }

    getDetailUrl() {
        return base_url;
    }
}

export class InvoiceLineAssignment extends Model {
    static type = 'invoice_item_assignment';
    KONTIERUNG_ID: number = -1;
    POSITION: number;
    BELEG_NR: number;
    MENGE: number;
    ART_LIEFERANT: number;
    ARTIKEL_NR: string;
    EINZEL_PREIS: number;
    MWST_SATZ: number;
    RABATT_SATZ: number;
    SKONTO: number;
    GESAMT_SUMME: number;
    KONTENRAHMEN_KONTO: string;
    WEITER_VERWENDEN: string = '0';
    VERWENDUNGS_JAHR: string;
    KONTIERUNGS_DATUM: string;
    KOSTENTRAEGER_TYP: string;
    KOSTENTRAEGER_ID: number;
    cost_unit: any;

    static applyPrototype(invoiceLineAssignment: InvoiceLineAssignment) {
        Object.setPrototypeOf(invoiceLineAssignment, InvoiceLineAssignment.prototype);
        if (invoiceLineAssignment.cost_unit) {
            Model.applyPrototype(invoiceLineAssignment.cost_unit);
        }
        return invoiceLineAssignment;
    }

    create() {
        return axios.post(base_url + '/api/v1/invoice-line-assignments', this);
    }

    save() {
        return axios.put(base_url + '/api/v1/invoice-line-assignments/' + this.getID(), this);
    }

    delete() {
        return axios.delete(base_url + '/api/v1/invoice-line-assignments/' + this.getID());
    }

    getID() {
        return this.KONTIERUNG_ID;
    }

    toString(): string {
        return this.GESAMT_SUMME + ' €';
    }

    fill(filler: InvoiceLine) {
        this.POSITION = filler.POSITION;
        this.BELEG_NR = filler.BELEG_NR;
        this.MENGE = filler.MENGE;
        this.MWST_SATZ = Number(filler.MWST_SATZ);
        this.SKONTO = Number(filler.SKONTO);
        this.RABATT_SATZ = Number(filler.RABATT_SATZ);
        this.EINZEL_PREIS = Number(filler.PREIS);
    }

    getEntityIcon(): string {
        return 'mdi-receipt';

    }

    getDetailUrl() {
        return base_url;
    }
}

export class InvoiceItem extends Model {
    static type = 'invoice_item';
    KATALOG_ID: number = -1;
    ART_LIEFERANT: string;
    ARTIKEL_NR: string;
    BEZEICHNUNG: string;
    LISTENPREIS: string;
    MWST_SATZ: number;
    RABATT_SATZ: number;
    SKONTO: number;
    EINHEIT: string;
    supplier: Partner;

    static applyPrototype(invoiceItem: InvoiceItem) {
        Object.setPrototypeOf(invoiceItem, InvoiceItem.prototype);
        if (invoiceItem.supplier) {
            Model.applyPrototype(invoiceItem.supplier);
        }
        return invoiceItem;
    }

    getID() {
        return this.KATALOG_ID;
    }

    toString(): string {
        return this.BEZEICHNUNG;
    }

    getEntityIcon(): string {
        return 'mdi-cart-outline';

    }

    getDetailUrl() {
        return base_url + '/katalog?option=preisentwicklung&lieferant=' + this.ART_LIEFERANT + '&artikel_nr=' + this.ARTIKEL_NR;
    }
}

export class BankAccountStandardChart extends Model {
    static type = 'bank_account_standard_chart';
    KONTENRAHMEN_ID: number = -1;
    NAME: string = '';
    booking_accounts: Array<BookingAccount> = [];

    static applyPrototype(chart: BankAccountStandardChart) {
        Object.setPrototypeOf(chart, BankAccountStandardChart.prototype);
        if (chart.booking_accounts) {
            chart.booking_accounts.forEach(v => {
                BookingAccount.applyPrototype(v);
            });
        }
        return chart;
    }

    getID() {
        return this.KONTENRAHMEN_ID;
    }

    toString(): string {
        return this.NAME;
    }

    getEntityIcon(): string {
        return 'mdi-table';

    }

    getDetailUrl() {
        return base_url;
    }
}

export class BookingAccount extends Model {
    static type = 'booking_account';
    KONTENRAHMEN_KONTO_ID: number = -1;
    KONTO: string = '';
    BEZEICHNUNG: string = '';

    static applyPrototype(account: BookingAccount) {
        Object.setPrototypeOf(account, BookingAccount.prototype);
        return account;
    }

    getID() {
        return this.KONTENRAHMEN_KONTO_ID;
    }

    toString(): string {
        return this.BEZEICHNUNG;
    }

    getEntityIcon(): string {
        return 'mdi-numeric';

    }

    getDetailUrl() {
        return base_url;
    }
}