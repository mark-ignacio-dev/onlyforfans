import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';

Vue.use(Vuex);

export default new Vuex.Store({

    state: {
        vault: {},
        vaultfolder: {},
        breadcrumb: [],
        shares: [],
        is_loading: true
    },

    mutations: {
        UPDATE_VAULT (state, payload) {
            state.vault = payload.hasOwnProperty('vault') ? payload.vault : [];
        },
        UPDATE_VAULTFOLDER (state, payload) {
            state.vaultfolder = payload.hasOwnProperty('vaultfolder') ? payload.vaultfolder : [];
        },
        UPDATE_BREADCRUMB (state, payload) {
            state.breadcrumb = payload.hasOwnProperty('breadcrumb') ? payload.breadcrumb : [];
        },
        UPDATE_SHARES (state, payload) { // shares for a vaultfolder
            state.shares = payload.hasOwnProperty('shares') ? payload.shares : [];
        },
        UPDATE_LOADING(state, payload) {
            state.is_loading = payload;
        },
    },

    actions: {
        getVault({ commit }, pkid) {
            const url = `/vaults/${pkid}`;
            axios.get(url).then( (response) => {
                commit('UPDATE_VAULT', response.data);
                commit('UPDATE_LOADING', false);
            });
        },
        getVaultfolder({ commit }, pkid) {
            const url = `/vaultfolders/${pkid}`;
            axios.get(url).then( (response) => {
                commit('UPDATE_VAULTFOLDER', response.data);
                commit('UPDATE_BREADCRUMB', response.data);
                commit('UPDATE_SHARES', response.data);
                commit('UPDATE_LOADING', false);
            });
        },
    },

    getters: {
        is_loading: state => state.is_loading, // indicates if Vuex has loaded data or not
        vault: state => state.vault,
        vaultfolder: state => state.vaultfolder,
        breadcrumb: state => state.breadcrumb,
        shares: state => state.shares,
        //children: state => state.vault.children, // Flat list
        //mediafiles: state => state.vault.mediafiles, // Flat list
    },
});

      /*
        // Find a specific movie by slug
        selected: (state) => (slug) => {
            return state.children.find(o => o.slug === slug);
        },
        */

      /*
        // List grouped by genre...
        genres: state => {
            var result = {};
            state.children.forEach( v => {
                v.genres.forEach( g => {
                    if ( !result.hasOwnProperty(g) ) {
                        result[g] = []; // init array for key g, for this genre (array holds movies in this genre)
                    }
                    result[g].push(v);
                });
            });
            return result;
        },
        */

