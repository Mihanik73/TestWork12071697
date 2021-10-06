<template>
    <div v-if="todoData">
        <li :class="{ completed: todoData.is_completed }">
            <div @click="changeTodoState(todoData.id, todoData.is_completed)" class="form-check">
                <label class="form-check-label">
                    <input class="checkbox" type="checkbox" :checked="todoData.is_completed == true"> {{ todoData.name }} <i class="input-helper"></i>
                </label>
            </div>
            <i @click="removeListItem(index, todoData.id)" class="remove">x</i>
        </li>
    </div>
    <div v-else>
        1
    </div>
</template>

<script>
    export default {
        props: {
            value: Object,
            index: Number,
            todoData: Object,
            // Methods
            removeListItem: Function,
            changeTodoState: Function
        },
        data() {
            return {

            }
        },
        computed: {
            //
        },
        methods: {

        }
    }
</script>