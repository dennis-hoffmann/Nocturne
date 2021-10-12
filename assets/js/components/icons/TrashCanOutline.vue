<template>
  <span :aria-hidden="decorative"
        :aria-label="title"
        class="material-design-icon trash-can-outline-icon"
        role="img"
  >
    <svg :fill="fillColor"
         class="material-design-icon__svg"
         :width="size"
         :height="size"
         viewBox="0 0 24 24">
      <path
          d="M9,3V4H4V6H5V19A2,2 0 0,0 7,21H17A2,2 0 0,0 19,19V6H20V4H15V3H9M7,6H17V19H7V6M9,8V17H11V8H9M13,8V17H15V8H13Z">
        <title v-if="!decorative">{{ title }}</title>
      </path>
    </svg>
  </span>
</template>

<script>
    export default {
        name: "TrashCanOutlineIcon",
        props: {
            title: {
                type: String,
                default: "Trash Can Outline icon"
            },
            decorative: {
                type: Boolean,
                default: false
            },
            fillColor: {
                type: String,
                default: "currentColor"
            },
            size: {
                type: Number,
                default: 24
            }
        }
    }
</script>
