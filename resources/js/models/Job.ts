import {ModelBase} from "./ModelBase";
import {Partner} from "./Partner";
import {Person} from "./Person";
import {JobTitle} from "./JobTitle";


export class Job extends ModelBase {
    static __typename = 'Job';
    id: number = -1;
    joinDate: Date;
    leaveDate: Date;
    holidays: number = 0;
    hourlyRate: number = 0;
    hoursPerWeek: number = 0;
    employer: Partner;
    employee: Person;
    title: JobTitle;

    getID() {
        return this.id;
    }
}