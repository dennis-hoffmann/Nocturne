import MediaSession from "../../mediaSession";

let store = null;

function init() {
    MediaSession.getSession().setActionHandler('play', (e) => {
        store.commit('player/play');
    });
    MediaSession.getSession().setActionHandler('pause', (e) => {
        store.commit('player/pause');
    });
    MediaSession.getSession().setActionHandler('nexttrack', (e) => {
        store.commit('player/next');
    });
    MediaSession.getSession().setActionHandler('previoustrack', (e) => {
        store.commit('player/previous');
    });
}

export default function createMediaSessionPlugin(rootStore) {
    store = rootStore;

    if (store.state.mediaSessionEnabled) {
        init()
    }
}