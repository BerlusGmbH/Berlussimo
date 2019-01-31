export class Active {

    getStartDateFieldName: Function;
    getEndDateFieldName: Function;

    public isActive(comparator: string = '=', dateString: string | null = null) {
        let date;
        if (!dateString) {
            date = new Date().toISOString().substr(0, 10);
        } else {
            date = dateString;
        }
        let start = this.getStartDateFieldName();
        let end = this.getEndDateFieldName();

        switch (comparator) {
            case '=':
                return start <= date
                    && (
                        end >= date
                        || (
                            end === '0000-00-00' || end === null
                        )
                    );
            case '>':
                return end > date
                    || (
                        end === '0000-00-00' || end === null
                    );
            case '<':
                return start < date;
            case '>=':
                return end >= date
                    || (
                        end === '0000-00-00' || end === null
                    );
            case '<=':
                return start <= date;
            default:
                return false;
        }
    }
}