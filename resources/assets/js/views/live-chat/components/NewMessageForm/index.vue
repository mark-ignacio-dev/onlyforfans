<template>
  <section v-if="!isLoading" class="conversation-footer d-flex flex-column">

    <div class="scheduled-message-head" v-if="isScheduled">
      <div>
        <fa-icon :icon="['fas', 'schedule']" class="fa-lg" fixed-width />
        <span> Scheduled for</span>
        <strong>{{ moment(deliverAtTimestamp * 1000).local().format('MMM DD, h:mm a') }}</strong>
      </div>
      <b-button variant="link" class="clickme_to-cancel" @click="clearScheduled">
        <fa-icon :icon="['fas', 'close']" class="clickable fa-lg" fixed-width />
      </b-button>
    </div>



    <b-form class="store-chatmessage mt-auto" @submit.prevent="sendMessage($event)">
      <VueDropzone
        ref="myVueDropzone"
        id="dropzone"
        :options="dropzoneOptions"
        include-styling
        useCustomSlot
        @vdropzone-file-added="onDropzoneAdded"
        @vdropzone-removed-file="onDropzoneRemoved"
        @vdropzone-sending="onDropzoneSending"
        @vdropzone-success="onDropzoneSuccess"
        @vdropzone-error="onDropzoneError"
        @vdropzone-queue-complete="onDropzoneQueueComplete"
        class="dropzone"
      >
        <!-- Photo Store display -->
        <div class="d-block w-100" v-if="selectedMediafiles.length > 0">
          <div class="d-flex">
            <b-btn variant="link" size="sm" class="ml-auto" @click="onClearFiles">
              {{ $t('clearFiles') }}
            </b-btn>
          </div>
          <UploadMediaPreview
            :mediafiles="selectedMediafiles"
            @change="changeMediafiles"
            @openFileUpload="openDropzone"
          />
        </div>

        <!-- Text area -->
        <div>
          <b-form-group>
            <b-form-textarea
              v-model="newMessageForm.mcontent"
              placeholder="Type a message..."
              :rows="mobile ? 2 : 3"
              max-rows="6"
              spellcheck="false"
            ></b-form-textarea>
          </b-form-group>
        </div>
      </VueDropzone>

      <!-- Bottom Toolbar -->
      <Footer
        @vaultSelect="toggleVaultSelect()"
        @openScheduleMessage="openScheduleMessageModal('set-scheduled')"
      />
    </b-form>

    <b-modal id="schedule-message-modal" hide-header centered hide-footer ref="schedule-message-modal">
      <div class="block-modal">
        <div class="header d-flex align-items-center">
          <h4 class="pt-1 pb-1">SCHEDULED MESSAGES</h4>
        </div>
        <div class="content">
          <b-form-datepicker
            v-model="newMessageForm.deliver_at.date"
            :state="newMessageForm.deliver_at.date ? true : null"
            :min="new Date()"
          />
          <b-form-timepicker
            v-model="newMessageForm.deliver_at.time"
            :state="newMessageForm.deliver_at.time ? true : null"
          ></b-form-timepicker>
        </div>
        <div class="d-flex align-items-center justify-content-end action-btns">
          <button class="link-btn" @click="clearScheduled">Cancel</button>
          <button class="link-btn" @click="setScheduled" >Apply</button>
        </div>
      </div>
    </b-modal>


  </section>
</template>

<script>
import { eventBus } from '@/app'
import Vuex from 'vuex'
import _ from 'lodash'
import moment from 'moment'

import VueDropzone from 'vue2-dropzone'

import UploadMediaPreview from '@components/posts/UploadMediaPreview'
import Footer from './Footer'

export default {
  name: 'NewMessageForm',

  components: {
    Footer,
    UploadMediaPreview,
    VueDropzone,
  },

  props: {
    session_user: null,
    chatthread_id: null,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    ...Vuex.mapState('messaging', [
      'selectedMediafiles',
      'uploadsVaultFolder',
    ]),

    channelName() {
      return `chatthreads.${this.chatthread_id}`
    },

    deliverAtTimestamp() {
      return this.isScheduled
        ? moment( `${this.newMessageForm.deliver_at.date} ${this.newMessageForm.deliver_at.time}` ).utc().unix()
        : null
    },

    isLoading() {
      return !this.session_user
    },

    isScheduled() {
      return this.newMessageForm.deliver_at.date && this.newMessageForm.deliver_at.time
    },

    dropzoneOptions() {
      return {
        url: this.$apiRoute('mediafiles.store'),
        paramName: 'mediafile',
        //acceptedFiles: 'image/*, video/*, audio/*',
        maxFiles: null,
        autoProcessQueue: false,
        thumbnailWidth: 100,
        //clickable: false, // must be false otherwise can't focus on text area to type (!)
        clickable: '.upload-files',
        maxFilesize: 15.9,
        addRemoveLinks: true,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
        },
      }
    },

  },

  data: () => ({

    moment: moment,

    newMessageForm: {
      mcontent: '',
      deliver_at: { date: null, time: null },
    },

    // If client is sending message
    sending: false,

  }), // data

  created() {
    this.isTyping = _.throttle(this._isTyping, 1000)
  },

  mounted() { },

  methods: {
    ...Vuex.mapMutations('messaging', [
      'ADD_SELECTED_MEDIAFILES',
      'CLEAR_SELECTED_MEDIAFILES',
      'UPDATE_SELECTED_MEDIAFILES',
      'REMOVE_SELECTED_MEDIAFILE_BY_INDEX',
    ]),

    ...Vuex.mapActions('messaging', [
      'getUploadsVaultFolder',
    ]),

    changeMediafiles(data) {
      this.UPDATE_SELECTED_MEDIAFILES([...data])
    },

    onClearFiles() {
      this.$refs.myVueDropzone.removeAllFiles()
      this.CLEAR_SELECTED_MEDIAFILES()
    },

    // ------------ Dropzone ------------------------------------------------ //

    openDropzone() {
      this.$refs.myVueDropzone.dropzone.hiddenFileInput.click();
    },

    onDropzoneAdded(file) {
      this.$log.debug('onDropzoneAdded', {file})
      if (!file.filepath) {
        this.ADD_SELECTED_MEDIAFILES({
          ...file,
          filepath: URL.createObjectURL(file),
        })
      } else {
        this.ADD_SELECTED_MEDIAFILES(file)
      }
    },

    onDropzoneRemoved() {},

    /** Add to Dropzone formData */
    onDropzoneSending(file, xhr, formData) {
      if ( !this.uploadsVaultFolder ) {
        throw new Error('Cancel upload, invalid upload folder');
      }
      formData.append('resource_id', this.uploadsVaultFolder.id)
      formData.append('resource_type', 'vaultfolders')
      formData.append('mftype', 'vault')
    },

    // Called each time the queue successfully uploads a file
    onDropzoneSuccess(file, response) {
      this.$log.debug('onDropzoneSuccess', { file, response })
      // Remove Preview
      if (file) {
        this.$refs.myVueDropzone.removeFile(file)
        this.removeFileFromSelected(file)
      }
      // Add Mediafile reference
      this.ADD_SELECTED_MEDIAFILES(response.mediafile)
    },

    onDropzoneError(file, message, xhr) {
      this.$log.error('Dropzone Error Event', { file, message, xhr })
      if (file) {
        this.$refs.myVueDropzone.removeFile(file)
        this.removeFileFromSelected(file)
      }
    },

    onDropzoneQueueComplete() {
      this.finalizeMessageSend()
    },

    openDropzone() {
      this.$refs.myVueDropzone.dropzone.hiddenFileInput.click();
    },

    removeFileFromSelected(file) {
      const index = _.findIndex(this.selectedMediafiles, mf => {
        return mf.upload ? mf.upload.filename === file.name : false
      })

      this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
    },

    //----------------------------------------------------------------------- //

    async finalizeMessageSend() {
      var params = {
        mcontent: this.newMessageForm.mcontent,
      }
      if (this.selectedMediafiles.length > 0) {
        params.attachments = this.selectedMediafiles
      }

      if (this.chatthread_id === 'new') {
        // %NOTE - Creating a new thread, delegate to parent template (CreateThreadForm), as
        //   that's where the selectedContact data resides
        params.is_scheduled = this.isScheduled
        if ( this.isScheduled ) {
          params.deliver_at = this.deliverAtTimestamp
        }
        this.$emit('create-chatthread', params)

      } else if (this.isScheduled) {
        // 'send' a pre-scheduled message (on an existing thread)
        params.deliver_at = this.deliverAtTimestamp
        await axios.post( this.$apiRoute('chatthreads.scheduleMessage', this.chatthread_id), params )
        this.$root.$bvToast.toast(
          this.$t('scheduled.message', { time: this.deliverAtTimestamp }),
          { variant: 'primary', title: this.$t('scheduled.title') }
        )
      } else {
        // send an immediate message (on an existing thread)
        const message = {
          chatthread_id: this.chatthread_id,
          mcontent: this.newMessageForm.mcontent,
          sender_id: this.session_user.id,
          is_delivered: true,
          imageCount: this.selectedMediafiles.length,
          created_at: this.moment().toISOString(),
          updated_at: this.moment().toISOString(),
        }
        this.$log.debug('messageForm sendMessage', { message })
        // Whisper the message to the channel so that is shows up for other users as fast as possible if they are
        //   currently viewing this thread
        this.$echo.join(this.channelName).whisper('sendMessage', { message })
        this.$emit('sendMessage', message)

        await axios.post( this.$apiRoute('chatthreads.sendMessage', this.chatthread_id), params )
      }

      this.clearForm()
      this.sending = false
    },

    async sendMessage(e) {
      this.sending = true
      // Validation check
      if (this.newMessageForm.mcontent === '' && this.selectedMediafiles.length === 0) {
        eventBus.$emit('validation', { message: this.$t('validation') })
        return
      }

      // Process any file in the queue
      const queued = this.$refs.myVueDropzone.getQueuedFiles()
      this.$log.debug('sendMessage dropzone queue', { queued })
      if (queued.length > 0) {
        await this.getUploadsVaultFolder()
        this.$refs.myVueDropzone.processQueue()
      } else {
        this.finalizeMessageSend()
      }

    },

    clearForm() {
      this.newMessageForm.mcontent = null
      this.CLEAR_SELECTED_MEDIAFILES()
      this.$refs.myVueDropzone.removeAllFiles()
      this.clearScheduled()
    },

    setScheduled: function() {
      this.$bvModal.hide('schedule-message-modal')
    },

    clearScheduled: function() {
      this.newMessageForm.deliver_at.date = null
      this.newMessageForm.deliver_at.time = null
      this.$bvModal.hide('schedule-message-modal')
    },

    doSomething() {
      // stub placeholder for impl
    },

    openScheduleMessageModal: function() {
      this.$refs['schedule-message-modal'].show();
    },

    toggleVaultSelect() {
      this.$emit('toggleVaultSelect')
    },

    _isTyping() {
      this.$echo.join(this.channelName)
        .whisper('typing', {
          name: this.session_user.name || this.session_user.username
        })
    },

  }, // methods

  watch: {
    'newMessageForm.mcontent': function(value) {
      if (
        this.newMessageForm.deliver_at.date === undefined || this.newMessageForm.deliver_at.date === null
        && this.newMessageForm.time === undefined || this.newMessageForm.time === null
      ) {
        this.isTyping()
      }
    },

  }, // watch



}
</script>

<style lang="scss" scoped>
.btn-link:hover {
  text-decoration: none;
}
.btn:focus, .btn.focus {
  box-shadow: none;
}

.conversation-footer {
  background-color: #fff;
  border-top: solid 1px rgba(138,150,163,.25);
}
button.clickme_to-submit_message {
  width: 9rem;
}

textarea,
.dropzone,
.vue-dropzone {
  border: none;
  &:hover {
    background-color: inherit;
  }
}

.dropzone.dz-started .dz-message {
  display: block;
}
.dropzone {
  padding: 0;
}

.dropzone .dz-message {
  width: 100%;
  text-align: center;
  margin: 0 !important;
}


</style>

<style lang="scss">
body {
  form.store-chatmessage {
    textarea.form-control {
      border: none;
    }
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "clearFiles": "Clear Images",
    "scheduled": {
      "title": "Scheduled",
      "message": "Messages has successfully been schedule to send at {time}"
    },
    "validation": "Please enter a message or select files to send"
  }
}
</i18n>