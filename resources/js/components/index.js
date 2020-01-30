import Vue from 'vue'
import Card from './Card'
import Child from './Child'
import Button from './Button'
import Link from './Link'
import Checkbox from './Checkbox'
import { HasError, AlertErrors, AlertError, AlertSuccess } from 'vform'

// Components that are registered globaly.
[
  Card,
  Child,
  Button,
  Link,
  Checkbox,
  HasError,
  AlertError,
  AlertErrors,
  AlertSuccess
].forEach(Component => {
  Vue.component(Component.name, Component)
})
