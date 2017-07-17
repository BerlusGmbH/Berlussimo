const base_url = window.location.origin;

Person.prototype.toString = function () {
    let full_name = '';
    if (this.name)
        full_name += this.name;
    if (this.name && this.first_name)
        full_name += ', ';
    if (this.first_name)
        full_name += this.first_name;
    return full_name;
};

Person.prototype.toIdentificator = function () {
    let id = this.getEntityIcon() + ' ' + String(this);
    if (this.getSexIcon() !== '') {
        id += ' ' + this.getSexIcon()
    }
    return id;
};

Person.prototype.getEntityIcon = function () {
    return '<i class="mdi ' + this.icon + '"></i>';
};

Person.prototype.getSexIcon = function () {
    if (this.sex === 'männlich') {
        return '<i class="mdi mdi-gender-male"></i>';
    }
    if (this.sex === 'weiblich') {
        return '<i class="mdi mdi-gender-female"></i>';
    }
    return '';
};

Person.prototype.getDetailUrl = function () {
    return base_url + '/personen/' + this.id;
};

Person.prototype.icon = 'mdi-account';

export function Person() {
}

Partner.prototype.toString = function () {
    return this.PARTNER_NAME;
};

Partner.prototype.getDetailUrl = function () {
    return base_url + '/partner/' + this.PARTNER_ID;
};

Partner.prototype.icon = 'mdi-account-multiple';

export function Partner() {
}

Objekt.prototype.toString = function () {
    return this.OBJEKT_KURZNAME;
};

Objekt.prototype.getDetailUrl = function () {
    return base_url + '/objekte/' + this.OBJEKT_ID;
};

Objekt.prototype.icon = 'mdi-city';

export function Objekt() {
}

Haus.prototype.toString = function () {
    return this.HAUS_STRASSE + ' ' + this.HAUS_NUMMER;
};

Haus.prototype.getDetailUrl = function () {
    return base_url + '/haeuser/' + this.HAUS_ID;
};

Haus.prototype.icon = 'mdi-domain';

export function Haus() {
}

Einheit.prototype.toString = function () {
    return this.EINHEIT_KURZNAME;
};

Einheit.prototype.getDetailUrl = function () {
    return base_url + '/partner/' + this.EINHEIT_ID;
};

Einheit.prototype.icon = 'mdi-cube';

export function Einheit() {
}