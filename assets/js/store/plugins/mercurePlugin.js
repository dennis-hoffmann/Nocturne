let store = null;

async function init() {
    const resp = await fetch(new URL(`${mercureHub}/subscriptions/general`), {credentials: "include"});
    const subscriptionCollection = await resp.json();

    const subscribeURL = new URL(mercureHub);
    subscribeURL.searchParams.append(
        'Last-Event-ID',
        subscriptionCollection.lastEventID
    );

    for (const topic of mercureTopics) {
        subscribeURL.searchParams.append('topic', topic);
    }

    // Subscribe to new 'general' subscriber events
    subscribeURL.searchParams.append(
        'topic',
        `${'/.well-known/mercure/subscriptions/general'}{/subscriber}`
    );

    const es = new EventSource(subscribeURL, { withCredentials: true });

    es.onmessage = ({data}) => {
        const update = JSON.parse(data);
        console.log(update);

        if (update['type'] === 'Subscription') {
            console.log('New Subscriber');

            return
        }

        // Only push updates with actual data
        if (update.data && update.data.message) {
            store.commit('mercure/pushUpdate', update.data);
        }
    };
}

export default function createMercurePlugin(rootStore) {
    store = rootStore;
    init()
}