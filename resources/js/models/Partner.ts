import {ModelBase} from "./ModelBase";

export class Partner extends ModelBase {
    static __typename = 'Partner';
    fullName: string = '';
    id: number = -1;
    streetName: string = '';
    streetNumber: string = '';
    postalCode: string = '';
    city: string = '';
    country: string = '';
    availableJobTitles: any[] = [];

    toString(): string {
        return this.fullName;
    }

    getEntityIcon(): string {
        return 'mdi-account-multiple';
    }

    getEntityIconTooltips(): Array<string> {
        return ['Partner'];
    }

    getAddress(): string {
        return this.streetName + ' ' + this.streetNumber + ', ' + this.postalCode + ' ' + this.city + ', ' + this.country;
    }

    getDetailUrl() {
        return Partner.getBaseURL() + '/partner?option=partner_im_detail&partner_id=' + this.id;
    }

    getID() {
        return this.id;
    }
}
