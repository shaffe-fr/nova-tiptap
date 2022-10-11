<template>
    <span>
        <Modal 
            :isActive="videoMenuIsActive"
            :hideMethod="hideVideoMenu"
        >
            <div 
                class="px-8 bg-white"
                style="padding-bottom: 32px;"
            >
                <div v-show="videoMode == 'url'">
                    <div class="flex flex-col">
                        <label 
                            class="text-sm mb-1 ml-1"
                            v-text="ttt('url')"
                        >
                        </label>

                        <input
                            class="
                                form-input
                                form-input-bordered
                                px-2 py-1 w-full
                                text-sm text-90
                                leading-none
                            "
                            type="text"
                            v-model="url"
                            placeholder="https://"
                        />

                        <div 
                            class="ml-1 mt-1 text-xs text-80"
                            v-text="ttt('external videos should start with http:// or https://')"
                        >
                        </div>

                        <div class="flex items-center mt-3">
                            <input 
                                :id="'openInNewWindow_'+field.attribute"
                                type="checkbox"
                                v-model="openInNewWindow"
                            />
                            <label
                                class="text-sm ml-2"
                                :for="'openInNewWindow_'+field.attribute"
                                v-text="ttt('open in new window')"
                            >
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <label 
                        class="block text-sm mb-1 ml-1"
                        v-text="ttt('custom css classes')"
                    >
                    </label>

                    <input
                        class="
                            form-input
                            form-input-bordered
                            px-2 py-1 w-full
                            text-sm text-90
                            leading-none
                        "
                        type="text"
                        v-model="extraClasses"
                    />

                    <label 
                        class="block text-sm mt-3 mb-1 ml-1"
                        v-text="ttt('title')"
                    >
                    </label>

                    <input
                        class="
                            form-input
                            form-input-bordered
                            px-2 py-1 w-full
                            text-sm text-90
                            leading-none
                        "
                        type="text"
                        v-model="title"
                    />
                </div>
            </div>

            <div class="bg-gray-200 px-8 py-3">   
                <div class="flex items-center justify-end">
                    <button
                        type="button"
                        class="
                            font-bold py-1 cursor-pointer
                            mr-4
                        "
                        @click="hideVideoMenu"
                        v-text="ttt('cancel')"
                    >
                    </button>

                    <button
                        type="button"
                        class="
                            relative bg-primary-500 text-white rounded
                            font-bold shadow py-1 px-4 cursor-pointer
                        "
                        :style="((videoMode == 'url' && !url) || (videoMode == 'file' && !file)) ? 'opacity: 0.5' : ''"
                        :disabled="(videoMode == 'url' && !url) || (videoMode == 'file' && !file)"
                        @click="videoMode == 'url' ? setVideoFromUrl($event) : uploadAndAddFile($event)"
                        v-text="videoMode == 'url' ? ttt('set video') : ttt('upload and video file')"
                    >
                    </button>
                </div>
            </div>


        
        </Modal>

        <span class="whitespace-nowrap">
            <base-button
                :isActive="videoIsActive"
                :isDisabled="!videoCanBeUsed"
                :clickMethod="showVideoMenu"
                :title="!videoIsActive ? ttt('set video') : ttt('edit video')"
                :icon="['fas', 'video']"
            >
            </base-button>

            <base-button
                :isActive="videoIsActive"
                :isDisabled="!videoCanBeUsed"
                :clickMethod="unsetVideo"
                :title="ttt('unset video')"
                :icon="['fas', 'unvideo']"
            >
            </base-button>
        </span>
    </span>
</template>

<script>
import buttonHovers from '../../mixins/buttonHovers';

import { library } from '@fortawesome/fontawesome-svg-core';

import { faTimesCircle } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import BaseButton from './BaseButton.vue';
import Modal from '../Modal.vue';
import Youtube from '@tiptap/extension-youtube'

import translations from '../../mixins/translations';

library.add(faTimesCircle);

export default {
    mixins: [buttonHovers, translations],

    props: [
        'button', 
        'editor',
        'field',
        'mode',
        'fileDisk',
        'filePath',
    ],

    data: function () {
        return {
            videoMenuIsActive: false,
            url: '',
            openInNewWindow: false,
            file: null,
            filename: null,
            uploadProgress: 0,
            uploading: false,
            extraClasses: '',
            videoMode: 'url',
            title: '',
            nofollow: false,
            noopener: false,
            noreferrer: false,
        }
    },

    components: {
        FontAwesomeIcon,
        BaseButton,
        Modal,
    },

    computed: {
        videoIsActive() {
           return this.editor ? this.editor.isActive('video') : false;
        },

        videoCanBeUsed() {
           return this.editor ? (this.mode == 'editor' && !this.editor.isActive('image')) : true;
        },

        withFileUpload() {
            return !this.field.videoSettings
                    ||
                    (
                        typeof(this.field.videoSettings.withFileUpload) != 'boolean'
                        || this.field.videoSettings.withFileUpload
                    );
        },
    },

    methods: {
        showVideoMenu() {
            if (this.videoIsActive) {
                let attributes = this.editor.getAttributes('video');
                this.url = attributes.href;
                this.openInNewWindow = attributes.target == '_blank' ? true : false;
                this.videoMode = attributes['tt-mode'] ? attributes['tt-mode'] : 'url';
                this.extraClasses = attributes.class ? attributes.class : '';
                this.title = attributes.title ? attributes.title : '';
                this.nofollow = attributes.rel && attributes.rel.indexOf('nofollow') > -1 ? true : false;
                this.noopener = attributes.rel && attributes.rel.indexOf('noopener') > -1 ? true : false;
                this.noreferrer = attributes.rel && attributes.rel.indexOf('noreferrer') > -1 ? true : false;
            } else {
                this.url = '';
                this.openInNewWindow = false;
                this.nofollow = false;
                this.noopener = false;
                this.noreferrer = false;
                this.videoMode = 'url';
                this.extraClasses = '';
                this.title = '';
            }
            
            this.videoMenuIsActive = true;
            
        },

        hideVideoMenu() {
            this.videoMenuIsActive = false;
        },

        removeFile() {
            this.file = null;
            this.filename = null;
            this.$refs.fileInput.value=null;
         },

         resetUploading() {
            this.uploading = false;
            this.uploadProgress = 0;
         },

         changeFile(files) {
            if (files.length) {
                this.file = files[0];
                this.filename = this.file.name;
            }
         },

        uploadAndAddFile(e) {
            e.preventDefault();

            this.uploading = true;
            
            let data = new FormData();
            data.append('file', this.file);   
            data.append('disk', this.fileDisk);   
            data.append('path', this.filePath);   

            const config = {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: progressEvent => this.uploadProgress = (progressEvent.loaded / progressEvent.total) * 100
            };

            axios.post('/nova-tiptap/api/file', data, config)
                .then(function (response) {
                    this.resetUploading();
                    this.removeFile();

                    let startPosition = response.data.url.lastIndexOf('/');
                    let filename = response.data.url.substr(startPosition + 1);
                    
                    let attributes = {
                        href: response.data.url,
                        'tt-mode': 'file',
                        download: filename,
                    };

                    this.setVideo(attributes);
                    
                    this.hideVideoMenu();
                }.bind(this))
                .catch(function (error) {
                    this.resetUploading();
                    this.removeFile();
                }.bind(this));
        },

        setVideoFromUrl(e) {
            e.preventDefault();

            let attributes = {
                href: this.url,
                'tt-mode': 'url',
            };

            if (this.openInNewWindow) {
                attributes.target = '_blank';
            } else {
                attributes.target = '_self';
            }

            this.setVideo(attributes);
            
            this.hideVideoMenu();
        },

        setVideo(attributes) {
            if (this.extraClasses) {
                attributes.class = this.extraClasses;
            }
            if (this.title) {
                attributes.title = this.title;
            }
            this.editor.commands.setYoutubeVideo({
                src: url,
                width: Math.max(320, parseInt(this.width, 10)) || 640,
                height: Math.max(180, parseInt(this.height, 10)) || 480,
            })
            
        },

        unsetVideo() {
            //this.editor.chain().focus().unsetVideo().run();
        }
    }
}
</script>
