import {ModelBase} from "./ModelBase";

export class Detail extends ModelBase {
    static __typename = 'Detail';
    id: number = -1;
    detailableType: string = '';
    detailableId: number = -1;
    value: string = '';
    comment: string = '';
    category: string = '';
    icon: string = 'mdi-note';

    toString(): string {
        return this.value;
    }

    getDetailUrl(): string {
        return Detail.getBaseURL();
    }

    getID() {
        return this.id;
    }
}