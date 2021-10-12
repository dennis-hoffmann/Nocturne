export default class Recommender {
    static async track(id) {
        const res = await fetch(`/recommendation/listen/${id}`, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        return res.json();
    }
}