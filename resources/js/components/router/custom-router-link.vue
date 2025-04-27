<template>
  <router-link v-slot="{ href, navigate, isActive, isExactActive }" :to="to" custom>
    <component
      :is="tag"
      :class="[
        baseClass,
        isActive && activeClass,
        isExactActive && exactActiveClass,
        customClass
      ]"
      :href="href"
      @click="navigate"
      v-bind="$attrs"
      v-on="$listeners"
    >
      <slot></slot>
    </component>
  </router-link>
</template>

<script>
export default {
  name: 'CustomRouterLink',
  inheritAttrs: false,
  props: {
    to: {
      type: [String, Object],
      required: true
    },
    tag: {
      type: String,
      default: 'a'
    },
    activeClass: {
      type: String,
      default: 'router-link-active'
    },
    exactActiveClass: {
      type: String,
      default: 'router-link-exact-active'
    },
    customClass: {
      type: String,
      default: ''
    },
    baseClass: {
      type: String,
      default: ''
    }
  }
}
</script>
