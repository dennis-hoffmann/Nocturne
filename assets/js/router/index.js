import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home'
import Detail from '../views/Detail'
import Library from '../views/Library'
import AlbumDetail from '../views/AlbumDetail'
import ArtistDetail from '../views/ArtistDetail'
import Playlists from "../views/Playlists";

const routes = [
    {
        path: '/app',
        name: 'Home',
        component: Home,
    },
    {
        path: '/app/detail',
        name: 'Detail',
        component: Detail,
    },
    {
        path: '/app/library',
        name: 'Library',
        component: Library,
    },
    {
        path: '/app/lists',
        name: 'Playlists',
        component: Playlists,
    },
    {
        path: '/app/album/:id',
        name: 'AlbumDetail',
        component: AlbumDetail,
        props: true,
    },
    {
        path: '/app/artist/:id',
        name: 'ArtistDetail',
        component: ArtistDetail,
        props: true,
    },
]

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
})

export default router