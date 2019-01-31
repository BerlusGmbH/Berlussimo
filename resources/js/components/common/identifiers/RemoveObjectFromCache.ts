export function removeObjectFromCache(data: any[] | Object, id: number | string) {
    if (Array.isArray(data)) {
        for (let key in data) {
            let value = data[key];
            if (value.type === 'id' && value.id === id) {
                data.splice(Number(key), 1);
            } else {
                removeObjectFromCache(value, id);
            }
        }
    } else if (typeof data === 'object' && data !== null) {
        for (let key in data) {
            let value = data[key];
            if (value && value.type === 'id' && value.id === id) {
                delete data[key];
            } else {
                removeObjectFromCache(value, id);
            }
        }
    }
}