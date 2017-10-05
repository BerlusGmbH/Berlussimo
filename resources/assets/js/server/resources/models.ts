import axios from "../../libraries/axios";

const base_url = window.location.origin;

export class Person {
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

    static type = 'Person';

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

    static prototypePerson(person: Person): Person {
        Object.setPrototypeOf(person, Person.prototype);
        Array.prototype.forEach.call(['common_details', 'hinweise', 'adressen', 'emails', 'faxs', 'phones'], (details) => {
            if (person[details]) {
                Object.setPrototypeOf(person[details], Detail.prototype);
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
                Object.setPrototypeOf(mietvertrag, RentalContract.prototype);
                if (mietvertrag.einheit) {
                    Object.setPrototypeOf(mietvertrag.einheit, Einheit.prototype);
                    if (mietvertrag.einheit.haus) {
                        Object.setPrototypeOf(mietvertrag.einheit.haus, Haus.prototype);
                        if (mietvertrag.einheit.haus.objekt) {
                            Object.setPrototypeOf(mietvertrag.einheit.haus.objekt, Objekt.prototype);
                        }
                    }
                }
            });
        }
        if (person.kaufvertraege) {
            Array.prototype.forEach.call(person.kaufvertraege, (kaufvertrag) => {
                Object.setPrototypeOf(kaufvertrag, PurchaseContract.prototype);
                if (kaufvertrag.einheit) {
                    Object.setPrototypeOf(kaufvertrag.einheit, Einheit.prototype);
                    if (kaufvertrag.einheit.haus) {
                        Object.setPrototypeOf(kaufvertrag.einheit.haus, Haus.prototype);
                        if (kaufvertrag.einheit.haus.objekt) {
                            Object.setPrototypeOf(kaufvertrag.einheit.haus.objekt, Objekt.prototype);
                        }
                    }
                }
            });
        }
        if (person.jobs_as_employee) {
            Array.prototype.forEach.call(person.jobs_as_employee, (job) => {
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
            });
        }
        return person;
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

    static type = 'Partner';

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

    static prototypePartner(partner: Partner) {
        Object.setPrototypeOf(partner, Partner.prototype);
        return partner;
    }
}

export class Objekt {
    OBJEKT_ID: number = -1;
    OBJEKT_KURZNAME: string = '';

    static type = 'HVObject';

    toString(): string {
        return this.OBJEKT_KURZNAME;
    }

    getEntityIcon(): string {
        return 'mdi-city';
    }

    getDetailUrl() {
        return base_url + '/objekte/' + this.OBJEKT_ID;
    }

    static prototypeObjekt(objekt: Objekt) {
        Object.setPrototypeOf(objekt, Objekt.prototype);
        return objekt;
    }
}

export class Haus {
    HAUS_ID: number = -1;
    HAUS_STRASSE: string = '';
    HAUS_NUMMER: string = '';
    HAUS_PLZ: number;
    HAUS_STADT: string = '';

    static type = 'House';

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

    static prototypeHaus(haus: Haus) {
        Object.setPrototypeOf(haus, Haus.prototype);
        return haus;
    }
}

export class Einheit {
    EINHEIT_ID: number = -1;
    EINHEIT_KURZNAME: string = '';
    EINHEIT_LAGE: string = '';
    EINHEIT_QM: number = 0;
    HAUS_ID: number = -1;
    TYP: string = '';

    static type = 'Unit';

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

    static prototypeEinheit(einheit: Einheit) {
        Object.setPrototypeOf(einheit, Einheit.prototype);
        return einheit;
    }
}

export class Detail {
    DETAIL_ID: number = -1;
    DETAIL_NAME: string = '';
    DETAIL_INHALT: string = '';
    DETAIL_BEMERKUNG: string = '';
    DETAIL_ZUORDNUNG_TABELLE: string = '';
    DETAIL_ZUORDNUNG_ID: string = '';

    static type = 'Detail';

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

    static prototypeDetail(detail: Detail) {
        Object.setPrototypeOf(detail, Detail.prototype);
        return detail;
    }
}

export class RentalContract {
    MIETVERTRAG_ID: number = -1;
    MIETVERTRAG_VON: Date;
    MIETVERTRAG_BIS: Date;
    EINHEIT_ID: number = -1;

    static type = 'RentalContract';

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

    static prototypeRentalContract(contract: RentalContract) {
        Object.setPrototypeOf(contract, RentalContract.prototype);
        return contract;
    }
}

export class PurchaseContract {
    ID: number = -1;
    VON: Date;
    BIS: Date;
    EINHEIT_ID: number = -1;

    static type = 'PurchaseContract';

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

    static prototypePurchaseContract(contract: PurchaseContract) {
        Object.setPrototypeOf(contract, PurchaseContract.prototype);
        return contract;
    }
}

export class Job {
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

    static type = 'Job';

    static prototypeJob(job: Job) {
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

export class JobTitle {
    id: number = -1;
    title: string = '';

    static type = 'JobTitle';

    toString(): string {
        return this.title;
    }

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

    static type = 'BankAccount';


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