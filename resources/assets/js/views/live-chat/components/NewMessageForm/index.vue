<template>
  <section v-if="!isLoading" class="conversation-footer d-flex flex-column">

    <div class="store-chatmessage mt-auto">
      <div class="d-flex flex-wrap align-items-stretch mb-3">
        <ScheduledAtDisplay
          v-if="isScheduled"
          :value="newMessageForm.deliver_at"
          class="w-auto mr-2"
          @open="openScheduleMessageModal"
          @clear="clearScheduled"
        />
        <TipDisplay
          v-if="hasTip"
          :value="tip"
          class="w-auto mr-2"
          @open="addTip"
          @clear="clearTip"
        />
        <SetPrice
          v-if="isSetPriceFormActive"
          v-model="newMessageForm.price"
          class="w-auto mr-2"
          @clear="clearPrice"
        />
      </div>

      <AudioRecorder
        v-if="showAudioRec"
        class="mb-3"
        @close="showAudioRec=false"
        @complete="audioRecordFinished"
      />

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
        @vdropzone-total-upload-progress="onDropzoneTotalUploadProgress"
        class="dropzone"
      >
        <!-- Photo Store display -->
        <div class="d-block w-100" v-if="selectedMediafiles && selectedMediafiles.length > 0">
          <div class="d-flex">
            <b-btn variant="link" size="sm" class="ml-auto" @click="onClearFiles">
              {{ $t('clearFiles') }}
            </b-btn>
          </div>
          <UploadMediaPreview
            :mediafiles="selectedMediafiles"
            @change="changeMediafiles"
            @openFileUpload="openDropzone"
            @remove="removeMediafileByIndex"
          />
        </div>

        <b-progress v-if="sending" :value="uploadProgress" max="100" animated class="my-2" />

        <!-- Text area -->
        <div class="mt-1">
          <b-form-group class="mb-2">
            <b-form-textarea
              class="message"
              v-model="newMessageForm.mcontent"
              placeholder="Type a message... (Ctrl + Enter to send)"
              :rows="mobile ? 2 : 3"
              max-rows="6"
              spellcheck="false"
              @keypress.enter="onEnterPress"
              :disabled="sending"
            ></b-form-textarea>
          </b-form-group>
        </div>
      </VueDropzone>

      <!-- Bottom Toolbar -->
      <Footer
        :selected="selectedOptions"
        :hasTip="hasTip"
        :hasPrice="hasPrice"
        :hasScheduled="hasScheduled"
        :isSending="sending"
        @vaultSelect="renderVaultSelector"
        @openScheduleMessage="openScheduleMessageModal"
        @recordAudio="recordAudio"
        @recordVideo="recordVideo"
        @setPrice="setPrice"
        @addTip="addTip"
        @submit="sendMessage($event)"
      />
    </div>

    <VideoRecorder
      v-if="showVideoRec"
      @close="showVideoRec = false"
      @complete="videoRecCompleted"
    />

    <b-modal v-model="scheduleMessageOpen" body-class="p-0" hide-header centered hide-footer>
      <ScheduleDateTime
        :scheduled_at="newMessageForm.deliver_at"
        @apply-schedule="date => newMessageForm.deliver_at = date"
        @edit-apply-schedule="date => newMessageForm.deliver_at = date"
        @close="scheduleMessageOpen = false"
      />
    </b-modal>
    <AddTip :receiver="participant" v-model="addTipOpen" @submit="tipAdded" />
  </section>
</template>

<script>
import { eventBus } from '@/eventBus'
import Vuex from 'vuex'
import _ from 'lodash'
import moment from 'moment'

import VueDropzone from 'vue2-dropzone'

import ScheduleDateTime from '@components/modals/ScheduleDateTime'
import UploadMediaPreview from '@components/posts/UploadMediaPreview'
import VideoRecorder from '@components/videoRecorder'
import AudioRecorder from '@components/audioRecorder'
import AddTip from './AddTip'
import TipDisplay from './TipDisplay'
import ScheduledAtDisplay from './ScheduledAtDisplay'

import SetPrice from './SetPrice.vue'
import Footer from './Footer'

//
//  sendMessage(): Footer form submit ||  press Ctrl + Enter
//  await this.getUploadsVaultFolder()
//  dropzone.processQueue()
//  finalizeMessageSend()
//
export default {
  name: 'NewMessageForm',

  components: {
    AddTip,
    AudioRecorder,
    Footer,
    ScheduledAtDisplay,
    ScheduleDateTime,
    SetPrice,
    TipDisplay,
    UploadMediaPreview,
    VideoRecorder,
    VueDropzone,
  },

  props: {
    session_user: null,
    chatthread_id: null,
    thread: { type: Object, default: () => ({}) },
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    ...Vuex.mapState('vault', [
      'selectedMediafiles',
      'uploadsVaultFolder',
    ]),
    ...Vuex.mapState('messaging', [ 'threads' ]),

    channelName() {
      return `chatthreads.${this.chatthread_id}`
    },

    participant() {
      if (!this.thread) {
        return null
      }
      // Find first participant that is not the session user
      return _.find(this.thread.participants, participant => participant.id !== this.session_user.id)
    },

    deliverAtTimestamp() {
      return this.isScheduled
        ? moment(this.newMessageForm.deliver_at).utc().unix()
        : null
    },

    isLoading() {
      return !this.session_user
    },

    isScheduled() {
      return this.newMessageForm.deliver_at !== null
    },

    dropzoneOptions() {
      return {
        url: this.$apiRoute('mediafiles.store'),
        paramName: 'mediafile',
        maxFiles: null,
        autoProcessQueue: false,
        thumbnailWidth: 100,
        clickable: '.upload-files', // button in the footer, see: https://www.dropzonejs.com/#configuration-options
        maxFilesize: 5000, // 5 GB

        // https://stackoverflow.com/questions/46379917/dropzone-js-upload-with-php-failed-after-30-seconds-upload
        timeout: 0, // disables timeout

        addRemoveLinks: true,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
        },
      }
    },

    selectedOptions() {
      var selected = []
        /*
      if (this.vaultOpen) {
        selected.push('vaultSelect')
      }
         */
      if (this.scheduleMessageOpen || this.hasScheduled) {
        selected.push('openScheduleMessage')
      }
      if (this.showVideoRec) {
        selected.push('recordVideo')
      }
      if (this.showAudioRec) {
        selected.push('recordAudio')
      }
      if (this.isSetPriceFormActive || this.hasPrice) {
        selected.push('setPrice')
      }

      return selected
    },

    hasTip() {
      return !(_.isEmpty(this.tip) || this.tip.amount === 0)
    },

    hasPrice() {
      return this.isSetPriceFormActive
    },

    hasScheduled() {
      return this.isScheduled
    },

  }, // computed

  data: () => ({

    moment: moment,

    newMessageForm: {
      mcontent: '',
      deliver_at: null,
      price: 0,
      currency: 'USD',
    },

    scheduleMessageOpen: false,
    showVideoRec: false,
    showAudioRec: false,

    isSetPriceFormActive: false,

    addTipOpen: false,
    tip: {},

    // If client is sending message
    sending: false,
    uploadProgress: 0,

  }), // data

  methods: {
    ...Vuex.mapMutations('vault', [
      'ADD_SELECTED_MEDIAFILES',
      'CLEAR_SELECTED_MEDIAFILES',
      'UPDATE_SELECTED_MEDIAFILES',
      'REMOVE_SELECTED_MEDIAFILE_BY_INDEX',
    ]),

    ...Vuex.mapActions('vault', [
      'getUploadsVaultFolder',
    ]),

    addTip() {
      this.addTipOpen = true
    },

    tipAdded(value) {
      this.tip = value
    },

    clearTip() {
      this.tip = {}
    },

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

    // %NOTE: called when adding files from disk, but *not* called when adding files from vault
    onDropzoneAdded(file) {
      let payload = { ...file, type: file.type }
      if (!file.filepath) {
        payload.filepath = URL.createObjectURL(file)
      }
      this.ADD_SELECTED_MEDIAFILES(payload)
      this.$nextTick(() => this.$forceUpdate())
    },

    // Appends to form data to effectively upload the files to a folder in the vault
    //  before attaching them to the message itself
    onDropzoneSending(file, xhr, formData) {
      if ( !this.uploadsVaultFolder ) {
        throw new Error('Cancel upload, invalid upload folder');
      }
      formData.append('resource_id', this.uploadsVaultFolder.id)
      formData.append('resource_type', 'vaultfolders')
      formData.append('mftype', 'vault')
    },

    onDropzoneTotalUploadProgress(totalUploadProgress, totalBytes, totalBytesSent) {
      this.uploadProgress = totalUploadProgress
    },

    // Called each time the queue successfully uploads a file
    // We have uploaded any files selected from disk to a 'temporary' vault folder...remove
    //  these files from the Dropzone queue, and add them to selected mediafiles which may already contain
    //  some pre-existing vault files that were selected
    // %NOTE:  user uploads a file in the message form, two [mediafiles] records are created: one
    //    with resource_type = ‘vaultfolders’ ,and a second with resource_type=‘messages’...former
    //    is not needed but is not cleaned up atm
    onDropzoneSuccess(file, response) {
      // Remove Preview
      if (file) {
        this.$refs.myVueDropzone.removeFile(file)
        this.removeFileFromSelected(file)
      }
      // Add Mediafile reference
      this.ADD_SELECTED_MEDIAFILES(response.mediafile)
    },

    onDropzoneError(file, message, xhr) {
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

    onDropzoneRemoved(file) {
      //const index = _.findIndex(this.selectedMediafiles, mf => {
      //  return mf.filepath === file.filepath
      //})
      //this.removeMediafileByIndex(index)
    },

    removeFileFromSelected(file) {
      const index = _.findIndex(this.selectedMediafiles, mf => {
        return mf.upload ? mf.upload.filename === file.name : false
      })
      this.removeMediafileByIndex(index)
    },

    // %NOTE: this can be called as a handler for the 'remove' event emitted by UploadMediaPreview
    removeMediafileByIndex(index) {
      console.log('NewMessageForm::removeMediafileByIndex()', {
        index,
      })
      if (index > -1)  {

        // If the file is in the Dropzone queue remove it from there as well
        let dzUUID = null
        if ( typeof this.selectedMediafiles[index] !== 'undefined' ) {
          const file = this.selectedMediafiles[index]
          if ( file.hasOwnProperty('upload') ) {
            dzUUID = file.upload.uuid
          }
        }

        if ( dzUUID !== null ) {
          // workaround...so we can also remove from Dropzone if its a disk file...
          this.$refs.myVueDropzone.getQueuedFiles().forEach( qf => {
            if ( qf.hasOwnProperty('upload') && qf.upload.uuid === dzUUID ) {
              this.$refs.myVueDropzone.removeFile(qf)
            }
          })
        }

        this.REMOVE_SELECTED_MEDIAFILE_BY_INDEX(index)
      }
    },

    //----------------------------------------------------------------------- //

    checkTip() {
      return new Promise((resolve) => {
       const callback = content => {
         resolve(content)
        }

        // Check for Tip
        if (this.hasTip) {
          // Validation
          if (this.tip.amount <= 0) {
            eventBus.$emit('validation', { message: this.$t('tipValidation')})
            this.sending = false
            // resolve with success == false
            resolve(false)
            return
          }
          // Open new payment form
          eventBus.$emit('open-modal', {
            key: 'render-tip',
            data: {
              skipMessage: true,
              tip: this.tip,
              wantsMessage: true,
              message: this.newMessageForm.mcontent,
              resource: this.thread.timeline, // TODO: should probably move this eventually.
              resource_type: 'timelines',
              callback,
            },
          })
        } else {
          // No tip so resolve with success
          resolve(true)
        }
      })
 
    },

    // Called when dropzone completes processing its queue, *OR* manually in 'sendMessage()'
    //   when sending a message without any attachments
    async finalizeMessageSend() {

      const tipPayload = await this.checkTip()
      this.$log.debug('check tip returned', { tipPayload })
      if (!tipPayload) {
        this.sending = false
        return
      }
      this.$log.debug('tipPayload.message: ', tipPayload.message)
      if (tipPayload.message) {
        // Tip Message was created
        // attach any mediafiles
        this.$log.debug('this.selectedMediafiles.length: ', this.selectedMediafiles.length)
        if (this.selectedMediafiles.length > 0) {
          const response = await this.axios.post(
            this.$apiRoute('chatmessages.attachMedia', { chatmessage: tipPayload.message }),
            { attachments: this.selectedMediafiles }
          )
          this.$log.debug('whisperMessage with mediafiles', { response: response.data.data })
          this.whisperMessage(response.data.data)
        } else {
          this.$log.debug('whisperMessage')
          this.whisperMessage(tipPayload.message)
        }
        this.$log.debug('clear form')
        this.clearForm() // removes mediafiles from store list and from Dropzone queue
        this.sending = false
        return
      }

      let params = {
        mcontent: this.newMessageForm.mcontent,
      }
      if (this.isSetPriceFormActive) {
        params.price    = this.newMessageForm.price
        params.currency = this.newMessageForm.currency
      }

      if (this.selectedMediafiles.length > 0) {
        params.attachments = this.selectedMediafiles
      }

      if (this.isScheduled) {
        params.is_scheduled = this.isScheduled
        params.deliver_at = this.deliverAtTimestamp
      }

      if (this.chatthread_id === 'new') {
        // %NOTE - Creating a new thread, delegate to parent template (CreateThreadForm), as
        //   that's where the selectedContact data resides
        params.is_scheduled = this.isScheduled
        if ( this.isScheduled ) {
          params.deliver_at = this.deliverAtTimestamp
        }
        this.$emit('create-chatthread', params)

      } else {
        const res = await axios.post( this.$apiRoute('chatthreads.addMessage', this.chatthread_id), params )
        if (!this.isScheduled) {
          this.whisperMessage(res.data.data)
        }

        if (this.isScheduled) {
          // Message was scheduled toast notification
          this.$root.$bvToast.toast(
            this.$t('scheduled.message', { time: moment(this.newMessageForm.deliver_at).local().format('h:mm a, MMM DD') }),
            { variant: 'primary', title: this.$t('scheduled.title') }
          )
        }
      }

      this.clearForm() // removes mediafiles from store list and from Dropzone queue
      this.sending = false

    }, // finalizeMessageSend()

    whisperMessage(newMessage) {
      this.$emit('sendMessage', newMessage)
      // Whisper the message to the channel so that is shows up for other users as fast as possible if they are
      //   currently viewing this thread
      this.$echo.join(this.channelName).whisper('sendMessage', { message: newMessage })
    },

    async sendMessage() {
      this.sending = true
      // Validation check
      const mediafileCount = this.selectedMediafiles ? this.selectedMediafiles.length : 0
      if (this.newMessageForm.mcontent === '' && mediafileCount === 0 && !this.hasTip) {
        eventBus.$emit('validation', { message: this.$t('validation') })
        this.sending = false
        return
      }
      if (this.newMessageForm.price > 0 && mediafileCount === 0) {
        eventBus.$emit('validation', { message: this.$t('pricedValidation')})
        this.sending = false
        return
      }

      // Process any file in the queue
      const queued = this.$refs.myVueDropzone.getQueuedFiles()
      if (queued.length > 0) {
        await this.getUploadsVaultFolder()
        this.$refs.myVueDropzone.processQueue() // when completed will call finalizeMessageSend()
      } else {
        this.finalizeMessageSend()
      }

    },

    // Send message on ctrl+enter
    onEnterPress(e) {
      if (e.ctrlKey) {
        this.sendMessage()
      }
    },

    clearForm() {
      this.newMessageForm.mcontent = ''
      this.clearPrice()
      this.CLEAR_SELECTED_MEDIAFILES()
      this.$refs.myVueDropzone.removeAllFiles()
      this.clearScheduled()
      this.clearTip()
    },

    clearPrice() {
      this.isSetPriceFormActive = false
      this.newMessageForm.price = 0
      this.newMessageForm.currency = 'USD'
    },

    setScheduled: function() {
      this.$bvModal.hide('schedule-message-modal')
    },

    clearScheduled: function() {
      this.newMessageForm.deliver_at = null
      this.$bvModal.hide('schedule-message-modal')
    },

    doSomething() {
      // stub placeholder for impl
    },

    openScheduleMessageModal() {
      this.scheduleMessageOpen = true
    },

    //toggleVaultSelect() {
    //this.$emit('toggleVaultSelect')
    //},
    renderVaultSelector() {
      eventBus.$emit('open-modal', {
        key: 'render-vault-selector',
        data: {
          context: 'create-message',
        },
      })
    },

    recordAudio() {
      this.showAudioRec = !this.showAudioRec
    },

    audioRecordFinished(file) {
      this.showAudioRec = false
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.addFile(file);
      }
    },

    recordVideo() {
      this.showVideoRec = !this.showVideoRec
    },

    videoRecCompleted(file) {
      this.showVideoRec = false;
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.addFile(file);
      }
    },

    setPrice() {
      // Toggle on click
      this.isSetPriceFormActive = !this.isSetPriceFormActive
    },

    _isTyping() {
      this.$echo.join(this.channelName)
        .whisper('typing', {
          name: this.session_user.name || this.session_user.username
        })
    },


  }, // methods

  mounted() {
   if ( this.$route.params.context ) {
     switch( this.$route.params.context ) {
       case 'send-selected-mediafiles-to-message': // we got here from the vault, with mediafiles to attach to a new message
         const mediafileIds = this.$route.params.mediafile_ids || []
         if ( mediafileIds.length ) {
           // Retrieve any 'pre-loaded' mediafiles, and add to dropzone...be sure to tag as 'ref-only' or something
           const response = axios.get(this.$apiRoute('mediafiles.index'), {
             params: {
               mediafile_ids: mediafileIds,
             },
           }).then( response => {
             response.data.data.forEach( mf => {
               this.ADD_SELECTED_MEDIAFILES(mf)
             })
           })
         }
         break
     } // switch
   }
  },

  created() {
    this.isTyping = _.throttle(this._isTyping, 1000)
  },

  beforeDestroy() {
    // Clear out any mediafiles so they don't get "carried" between threads before send is clicked
    this.CLEAR_SELECTED_MEDIAFILES()
    this.$refs.myVueDropzone.removeAllFiles()
  },

  watch: {
    'newMessageForm.mcontent': function(value) {
      if (this.newMessageForm.deliver_at === undefined || this.newMessageForm.deliver_at === null) {
        if (value) {
          this.isTyping()
        }
      }
    },
    isSetPriceFormActive(val,last) {
      // if form goes from active to not-active, clear out price inputs
      if (val===last) {
        return
      }
      if (!val) {
        this.clearPrice()
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

textarea {
  overflow-y: auto !important;
}

.dropzone.dz-started .dz-message {
  display: block;
}
.dropzone {
  padding: 0;
  min-height: 0 !important;
}

.dropzone .dz-message {
  width: 100%;
  text-align: center;
  margin: 0 !important;
}

textarea.form-control {
  border: solid 1px #dfdfdf;
  overflow-y: auto;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "clearFiles": "Clear Images",
    "pricedValidation": "Messages with a set unlock price must contain media (photo or video). Please attach and resend.",
    "scheduled": {
      "title": "Scheduled",
      "message": "Messages has successfully been scheduled to send at {time}."
    },
    "tipValidation": "Tip cannot be zero.",
    "validation": "Please enter a message or select files to send.",
  }
}
</i18n>
