/**
 * js/store/payments.js
 * Vuex store module for items related to payments
 */
import _ from 'lodash'
import { axios } from 'vue/types/umd'
import propSelect from '@helpers/propSelect'

const route = window.route

export const payments = {
  namespaced: true,

  state: () => ({
    savedPaymentMethods: [],
  }),

  getters: {
    /**
     * Gets saved cards
     * @returns {(Object[])}
     */
    savedCards(state) {
      return _.filter(state.savedPaymentMethods, method => method.type === 'card' )
    }
  },

  mutations: {
    UPDATE_SAVED_PAYMENT_METHODS(state, payload) {
      state.savedPaymentMethods = propSelect(payload, 'paymentMethods')
    },
  },

  actions: {
    /**
     * Gets user's saved cards
     */
    getSavedCards({ dispatch, getters }) {
      return new Promise((resolve, reject) => {
        if (getters.savedCards.length > 0) {
          resolve(getters.savedCards)
        }
        dispatch('updateSavedPaymentMethods')
          .then(() => {
            resolve(getters.savedCards)
          })
          .catch(error => reject(error))
      })
    },

    /**
     * Gets all user's saved payment methods
     */
    getSavedPaymentMethods({ dispatch, state }) {
      return new Promise((resolve, reject) => {
        if (state.savedPaymentMethods.length > 0) {
          resolve(state.savedPaymentMethods)
        }
        dispatch('updateSavedPaymentMethods')
          .then(() => {
            resolve(state.savedPaymentMethods)
          })
          .catch(error => reject(error))
      })
    },

    /**
     * Updates the saved payment methods from the server
     */
    updateSavedPaymentMethods({ commit }) {
      return new Promise((resolve, reject) => {
        axios.get(route('payment.paymentMethods.get', {}))
          .then(response => {
            commit('UPDATE_SAVED_PAYMENT_METHODS', response.data)
            resolve()
          })
          .catch(error => reject(error))
      })
    },
  },
}

