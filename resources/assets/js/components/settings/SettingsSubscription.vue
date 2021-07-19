<template>
  <div v-if="!isLoading">
    <b-card title="Subscription">
      <b-card-text>
        <b-form @submit.prevent="submitSubscriptions($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formSubscriptions">
            <b-row>
              <b-col>
                <FormTextInput itype="currency" ikey="subscriptions.price_per_1_months"  v-model="formSubscriptions.subscriptions.price_per_1_months"  label="Price per Month" :verrors="verrors" />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_3_months"  v-model="formSubscriptions.subscriptions.price_per_3_months"  label="Price per 3 Months" :verrors="verrors" />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_6_months"  v-model="formSubscriptions.subscriptions.price_per_6_months"  label="Price per 6 Months" :verrors="verrors" />
                <FormTextInput itype="currency" ikey="subscriptions.price_per_12_months" v-model="formSubscriptions.subscriptions.price_per_12_months" label="Price per Year" :verrors="verrors" />
              </b-col>
              <b-col>
                <b-form-group id="group-is_follow_for_free" label="Follow for Free?" label-for="is_follow_for_free">
                  <b-form-checkbox
                    id="is_follow_for_free"
                    v-model="formSubscriptions.is_follow_for_free"
                    name="is_follow_for_free"
                    value=1
                    unchecked-value=0
                    switch size="lg"></b-form-checkbox>
                </b-form-group>
              </b-col>
            </b-row>
          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div class="w-100 d-flex justify-content-end">
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Profile promotion campaign" class="mt-5">
      <b-row class="mt-3">
        <b-col>
          <p><small class="text-muted">Offer a free trial or a discounted subscription on your profile for a limited number of new or already expired subscribers.</small></p>
          <div class="w-100 d-flex justify-content-center">
            <b-button @click="startCampaign" class="w-25 ml-3" variant="primary">Start Promotion Campaign</b-button>
          </div>
        </b-col>
      </b-row>

      <b-row v-if="activeCampaign">
        <b-col class="mt-3">
          <h5 v-if="activeCampaign.type === 'trial'">Limited offer - Free trial for {{ activeCampaign.trial_days }} days!</h5>
          <h5 v-if="activeCampaign.type === 'discount'">Limited offer - {{ activeCampaign.discount_percent }} % off for 31 days!</h5>
          <p><small class="text-muted">For {{ campaignAudience }} • ends {{ campaignExpDate }} • left {{ activeCampaign.subscriber_count }}</small></p>
          <b-button @click="showStopModal" class="w-25 ml-3" variant="primary">Stop Promotion</b-button>
        </b-col>
      </b-row>
    </b-card>

    <b-modal id="modal-stop-campaign" v-model="isStopModalVisible" size="md" title="Stop Promotion Campaign" >
      <p>Are you sure you want to stop your profile promotion campaign?</p>
      <template #modal-footer>
        <b-button @click="hideStopModal" type="cancel" variant="secondary">Cancel</b-button>
        <b-button @click="stopCampaign" variant="danger">Stop Campaign</b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
import moment from 'moment'
import Vuex from 'vuex'
import { eventBus } from '@/app'
import FormTextInput from '@components/forms/elements/FormTextInput'

export default {
  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user_settings
    },

    campaignAudience() {
      if (this.activeCampaign) {
        const { has_new: hasNew, has_expired: hasExpired } = this.activeCampaign

        if (hasNew && hasExpired) {
          return 'new & expired subscribers'
        }

        if (hasNew) {
          return 'new subscribers'
        }

        if (hasExpired) {
          return 'expired subscribers'
        }
      }

      return null
    },

    campaignExpDate() {
      if (this.activeCampaign) {
        const { created_at: createdAt, offer_days: offerDays } = this.activeCampaign
        const startDate = moment(createdAt)
        const expDate = startDate.add(offerDays, 'days')
        return expDate.format('MMM D')
      }

      return null
    }
  },

  data: () => ({
    isSubmitting: {
      formSubscriptions: false,
    },

    verrors: null,

    formSubscriptions: {
      is_follow_for_free: null,
      subscriptions: { // cattrs
        price_per_1_months: null,
        price_per_3_months: null,
        price_per_6_months: null,
        price_per_12_months: null,
        referral_rewards: '',
      },
    },

    activeCampaign: null,
    isStopModalVisible: false,
  }),

  watch: {
    user_settings(newVal) {
      if ( newVal.cattrs.subscriptions ) {
        this.formSubscriptions.subscriptions = newVal.cattrs.subscriptions
      }
      if ( newVal.hasOwnProperty('is_follow_for_free') ) {
        this.formSubscriptions.is_follow_for_free = newVal.is_follow_for_free ? 1 : 0
      }
    },
  },

  mounted() {
  },

  created() {
    if ( this.user_settings.cattrs.subscriptions ) {
      this.formSubscriptions.subscriptions = this.user_settings.cattrs.subscriptions
    }
    if ( this.user_settings.hasOwnProperty('is_follow_for_free') ) {
      this.formSubscriptions.is_follow_for_free = this.user_settings.is_follow_for_free ? 1 : 0
    }

    eventBus.$on('campaign-updated', campaign => {
      this.activeCampaign = campaign
    })

    this.getActiveCampaign()
  },

  methods: {
    ...Vuex.mapActions(['getMe']),

    async submitSubscriptions(e) {
      this.isSubmitting.formSubscriptions = true

      try {
        const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formSubscriptions)
        this.verrors = null
        this.onSuccess()
      } catch(err) {
        this.verrors = err.response.data.errors
      }

      this.isSubmitting.formSubscriptions = false
    },

    async getActiveCampaign() {
      const response = await axios.get(this.$apiRoute('campaigns.active'))
      this.activeCampaign = response.data.data
    },

    startCampaign() {
      eventBus.$emit('open-modal', {
        key: 'modal-promotion-campaign',
      })
    },

    showStopModal() {
      this.isStopModalVisible = true
    },

    hideStopModal() {
      this.isStopModalVisible = false
    },

    async stopCampaign() {
      await axios.post(this.$apiRoute('campaigns.stop'))
      this.activeCampaign = null
      this.hideStopModal()
    },

    onReset(e) {
      e.preventDefault()
    },

    onSuccess() {
      this.$root.$bvToast.toast('Settings have been updated successfully!', {
        toaster: 'b-toaster-top-center',
        title: 'Success',
        variant: 'success',
      })
    }
  },

  components: {
    FormTextInput,
  },
}
</script>

<style scoped>
</style>
