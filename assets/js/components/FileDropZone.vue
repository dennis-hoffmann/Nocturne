<template>
    <div class="upload-container" role="main">
        <form ref="form" method="post" enctype="multipart/form-data" novalidate class="box has-advanced-upload">
            <div class="box-input">
                <svg class="box-icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43">
                    <path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"/>
                </svg>
                <input ref="input" type="file" :name="name" id="file" class="box-file"/>
                <label for="file">
                    <span>
                        <strong>Choose a file</strong><span class="box-dragndrop"> or drag it here</span>.
                    </span>
                </label>
                <button type="submit" class="box-button">Upload</button>
            </div>
            <div class="box-uploading">Uploading&hellip;</div>
            <div class="box-success">Done</div>
            <div class="box-error">Error</div>
        </form>

        <div id="file-previews" class="row mt-2">
            <div v-for="preview in previews" class="col-4 mx-auto">
                <img class="img-fluid" :src="preview"/>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'FileDropZone',
        data() {
            return {
                files: [],
                previews: [],
            }
        },
        emits: ['submit'],
        props: {
            name: {
                type: String,
                default: 'files[]'
            },
            limit: {
                type: Number,
                default: 1
            },
        },
        mounted() {
            const form = this.$refs.form;

            ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach((listener) => {
                form.addEventListener(listener, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });
            ['dragover', 'dragenter'].forEach((e) => {
                form.addEventListener(e, () => {
                    form.classList.add('is-dragover');
                });
            });
            ['dragleave', 'dragend', 'drop'].forEach((e) => {
                form.addEventListener(e, () => {
                    form.classList.remove('is-dragover');
                });
            });
            form.addEventListener('drop', (e) => {
                if (this.files.length === this.limit) {
                    alert(`Limit of ${this.limit} files would be exceeded.`);
                    return false;
                }

                // Todo validate file types:
                // https://attacomsian.com/blog/uploading-files-using-fetch-api#validate-file-type--size

                e.dataTransfer.files.forEach((file) => {
                    this.files.push(file);

                    const reader = new FileReader();
                    reader.addEventListener('loadend', (e) => {
                        this.previews.push(reader.result);
                    });
                    reader.readAsDataURL(file)
                })

            });

            form.addEventListener('submit', (e) => {
                e.preventDefault();

                this.$emit('submit', this.files);

                form.classList.add('is-uploading');
            });
        }
    }
</script>

<style scoped>
    /* I'm no Frontend Dev */
    .upload-container {
        width: 100%;
        max-width: 680px; /* 800 */
        text-align: center;
        margin: 0 auto;
    }

    .box {
        font-size: 1.25rem; /* 20 */
        background-color: #c8dadf;
        position: relative;
        padding: 20px;
    }

    .box.has-advanced-upload {
        outline: 2px dashed #92b0b3;
        outline-offset: -10px;

        -webkit-transition: outline-offset .15s ease-in-out, background-color .15s linear;
        transition: outline-offset .15s ease-in-out, background-color .15s linear;
    }

    .box.is-dragover {
        outline-offset: -20px;
        outline-color: #c8dadf;
        background-color: #fff;
    }

    .box-file {
        display: none;
    }

    .box-dragndrop,
    .box-icon {
        display: none;
    }

    .box.has-advanced-upload .box-dragndrop {
        display: inline;
    }

    .box.has-advanced-upload .box-icon {
        width: 100%;
        height: 80px;
        fill: #92b0b3;
        display: block;
        margin-bottom: 40px;
    }

    .box.is-uploading .box-input,
    .box.is-success .box-input,
    .box.is-error .box-input {
        visibility: hidden;
    }

    .box-uploading,
    .box-success,
    .box-error {
        display: none;
    }

    .box.is-uploading .box-uploading,
    .box.is-success .box-success,
    .box.is-error .box-error {
        display: block;
        position: absolute;
        top: 50%;
        right: 0;
        left: 0;

        -webkit-transform: translateY(-50%);
        transform: translateY(-50%);
    }

    .box-uploading {
        font-style: italic;
    }

    .box-success {
        -webkit-animation: appear-from-inside .25s ease-in-out;
        animation: appear-from-inside .25s ease-in-out;
    }

    @-webkit-keyframes appear-from-inside {
        from {
            -webkit-transform: translateY(-50%) scale(0);
        }
        75% {
            -webkit-transform: translateY(-50%) scale(1.1);
        }
        to {
            -webkit-transform: translateY(-50%) scale(1);
        }
    }

    @keyframes appear-from-inside {
        from {
            transform: translateY(-50%) scale(0);
        }
        75% {
            transform: translateY(-50%) scale(1.1);
        }
        to {
            transform: translateY(-50%) scale(1);
        }
    }

    .box-restart {
        font-weight: 700;
    }

    .box-restart:focus,
    .box-restart:hover {
        color: #39bfd3;
    }

    .js .box-file {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }

    .js .box-file + label {
        max-width: 80%;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        display: inline-block;
        overflow: hidden;
    }

    .js .box-file + label:hover strong,
    .box-file:focus + label strong,
    .box-file.has-focus + label strong {
        color: #39bfd3;
    }

    .js .box-file:focus + label,
    .js .box-file.has-focus + label {
        outline: 1px dotted #000;
        outline: -webkit-focus-ring-color auto 5px;
    }

    .js .box-file + label * {
        /* pointer-events: none; */ /* in case of FastClick lib use */
    }

    .no-js .box-file + label {
        display: none;
    }

    .no-js .box-button {
        display: block;
    }

    .box-button {
        font-weight: 700;
        color: #e5edf1;
        background-color: #39bfd3;
        display: block;
        padding: 8px 16px;
        margin: 40px auto 0;
    }

    .box-button:hover,
    .box-button:focus {
        background-color: #0f3c4b;
    }
</style>