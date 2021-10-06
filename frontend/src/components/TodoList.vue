<template>
    <div class="container">
        <h1>TODO List</h1>
        <div class="page-content page-container" id="page-content">
            <div class="padding">
                <div class="row container d-flex justify-content-center">
                    <div class="col-md-12">
                        <div class="card px-3">
                            <div class="card-body">
                                <div class="add-items d-flex"> <input type="text" v-model="newTodoName" class="form-control todo-list-input" placeholder="What do you need to do today?">
                                    <button @click.prevent="addListItem" class="add btn btn-primary font-weight-bold todo-list-add-btn">Add</button>
                                </div>
                                <div class="list-wrapper">
                                    <ul class="d-flex flex-column-reverse todo-list">
                                        <template v-for="(todo, i) in items" :key="i">
                                            <TodoRow
                                                v-model:todoData="items[i]"
                                                :index="i"
                                                v-bind:removeListItem="removeListItem"
                                                v-bind:changeTodoState="changeTodoState"
                                            ></TodoRow>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import TodoRow from './TodoRow.vue'

    axios.defaults.withCredentials = true
    axios.defaults.baseURL = `http://${process.env.VUE_APP_BACKEND_APP_URL}:${process.env.VUE_APP_BACKEND_APP_PORT}`

    export default {
        components: {
            TodoRow
        },
        data() {
            return {
                todos: [],
                newTodoName: ''
            }
        },
        computed: {
            items() {
                return this.todos ?? []
            },
            $api() {
                return new TodoApi(axios)
            }
        },
        methods: {
            getTodosList() {
                return this.$api.getList()
            },
            async addListItem() {
                if (this.newTodoName === '') {
                    return;
                }

                let newTodo = null

                try {
                    let todoId = await this.$api.create(this.newTodoName)

                    if (todoId !== null) {
                        newTodo = new TaskFactory(todoId, this.newTodoName)
                            .build()
                    }

                } catch (error) {
                    console.error('New task is not created')
                }

                if (newTodo) {
                    this.todos.push(newTodo)

                    this.newTodoName = ''
                }
            },
            removeListItem(index, id) {
                this.todos.splice(index, 1)

                this.$api.delete(id)
            },
            changeTodoState(id, oldState) {
                console.log(oldState, !oldState)
                this.$api.changeTodoState(id, !oldState)
            }
        },
        async beforeMount() {
            await this.$api.apiAuthRequest()

            let todos = await this.$api.getList()

            this.todos = Object.keys(todos).map((key) => {
                return todos[key]
            })
        },
    }

    class TodoApi {
        constructor(axios) {
            this.$apiClient = axios
        }

        async getList() {
            let result = []

            await axios.get('/api/todos/').then((response) => {
                if (response?.data) {
                    result = response.data
                }
            })

            return result
        }

        async create(name) {
            let resultId = null

            await this.$apiClient.post('/api/todos/', { name: name }).then((response) => {
                if (response?.data?.id) {
                    resultId = response.data.id
                }
            })

            return resultId
        }

        async changeTodoState(id, state) {
            this.$apiClient.put(`/api/todos/${id}`, { is_completed: state }).then((response) => {
                console.log(response, 'newState for ' + id + ' is ' + state)
            })
        }

        async delete(id) {
            this.$apiClient.delete(`/api/todos/${id}`).then((response) => {
                console.log(response, 'deleted')
            })
        }

        async apiAuthRequest() {
            return axios.get(`/sanctum/csrf-cookie`, { withCredentials: true })
        }
    }

    class TaskFactory {
        id            = null
        name          = ''
        is_completed  = false

        constructor(id, name) {
            this.id = id
            this.name = name
        }

        build() {
            let todoData = {
                id:           this.id,
                name:         this.name,
                is_completed: this.is_completed,
            }

            return new Task({...todoData})
        }
    }

    class Task {
        constructor(data) {
            this.id           = data.id
            this.name         = data.name
            this.is_completed = data.is_completed
        }
    }
</script>

<style>
h3 {
    margin: 40px 0 0;
}
ul {
    list-style-type: none;
    padding: 0;
}
li {
    display: inline-block;
    margin: 0 10px;
}
a {
    color: #42b983;
}
body {
    background-color: #f9f9fa
}

.flex {
    -webkit-box-flex: 1;
    -ms-flex: 1 1 auto;
    flex: 1 1 auto
}

@media (max-width:991.98px) {
    .padding {
        padding: 1.5rem
    }
}

@media (max-width:767.98px) {
    .padding {
        padding: 1rem
    }
}

.padding {
    padding: 5rem
}

.card {
    box-shadow: none;
    -webkit-box-shadow: none;
    -moz-box-shadow: none;
    -ms-box-shadow: none
}

.pl-3,
.px-3 {
    padding-left: 1rem !important
}

.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid #d2d2dc;
    border-radius: 0
}

.pr-3,
.px-3 {
    padding-right: 1rem !important
}

.card .card-body {
    padding: 1.25rem 1.75rem
}

.card-body {
    flex: 1 1 auto;
    padding: 1.25rem
}

.card .card-title {
    color: #000000;
    margin-bottom: 0.625rem;
    text-transform: capitalize;
    font-size: 0.875rem;
    font-weight: 500
}

.add-items {
    margin-bottom: 1.5rem;
    overflow: hidden
}

.d-flex {
    display: flex !important
}

.add-items input[type="text"] {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    width: 100%;
    background: transparent
}

.form-control {
    border: 1px solid #f3f3f3;
    font-weight: 400;
    font-size: 0.875rem
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.875rem 1.375rem;
    font-size: 1rem;
    line-height: 1;
    color: #495057;
    background-color: #ffffff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 2px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out
}

.add-items .btn {
    margin-left: .5rem
}

.btn {
    font-size: 0.875rem;
    line-height: 1;
    font-weight: 400;
    padding: .7rem 1.5rem;
    border-radius: 0.1275rem
}

.list-wrapper {
    height: 100%;
    max-height: 100%
}

.add-items {
    margin-bottom: 1.5rem;
    overflow: hidden
}

.add-items input[type="text"] {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    width: 100%;
    background: transparent
}

.add-items .btn,
.add-items .fc button,
.fc .add-items button,
.add-items .ajax-upload-dragdrop .ajax-file-upload,
.ajax-upload-dragdrop .add-items .ajax-file-upload,
.add-items .swal2-modal .swal2-buttonswrapper .swal2-styled,
.swal2-modal .swal2-buttonswrapper .add-items .swal2-styled,
.add-items .wizard>.actions a,
.wizard>.actions .add-items a {
    margin-left: .5rem
}

.rtl .add-items .btn,
.rtl .add-items .fc button,
.fc .rtl .add-items button,
.rtl .add-items .ajax-upload-dragdrop .ajax-file-upload,
.ajax-upload-dragdrop .rtl .add-items .ajax-file-upload,
.rtl .add-items .swal2-modal .swal2-buttonswrapper .swal2-styled,
.swal2-modal .swal2-buttonswrapper .rtl .add-items .swal2-styled,
.rtl .add-items .wizard>.actions a,
.wizard>.actions .rtl .add-items a {
    margin-left: auto;
    margin-right: .5rem
}

.list-wrapper {
    height: 100%;
    max-height: 100%
}

.list-wrapper ul {
    padding: 0;
    text-align: left;
    list-style: none;
    margin-bottom: 0
}

.list-wrapper ul li {
    font-size: .9375rem;
    padding: .4rem 0;
    border-bottom: 1px solid #f3f3f3
}

.list-wrapper ul li:first-child {
    border-bottom: none
}

.list-wrapper ul li .form-check {
    max-width: 90%;
    margin-top: .25rem;
    margin-bottom: .25rem
}

.list-wrapper ul li .form-check label:hover {
    cursor: pointer
}

.list-wrapper input[type="checkbox"] {
    margin-right: 15px
}

.list-wrapper .remove {
    cursor: pointer;
    font-size: 1.438rem;
    font-weight: 600;
    width: 1.25rem;
    height: 1.25rem;
    line-height: 20px;
    text-align: center
}

.list-wrapper .completed > div {
    text-decoration: line-through;
    text-decoration-color: #3da5f4
}

.list-wrapper ul li .form-check {
    max-width: 90%;
    margin-top: .25rem;
    margin-bottom: .25rem
}

.list-wrapper ul li .form-check,
.list-wrapper ul li .form-check .form-check-label,
.email-wrapper .mail-sidebar .menu-bar .profile-list-item a .user .u-name,
.email-wrapper .mail-sidebar .menu-bar .profile-list-item a .user .u-designation,
.email-wrapper .mail-list-container .mail-list .content .sender-name,
.email-wrapper .message-body .attachments-sections ul li .details p.file-name,
.settings-panel .chat-list .list .info p {
    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 100%;
    white-space: nowrap
}

.form-check {
    position: relative;
    display: block;
    margin-top: 10px;
    margin-bottom: 10px;
    padding-left: 0
}

.list-wrapper ul li .form-check,
.list-wrapper ul li .form-check .form-check-label,
.email-wrapper .mail-sidebar .menu-bar .profile-list-item a .user .u-name,
.email-wrapper .mail-sidebar .menu-bar .profile-list-item a .user .u-designation,
.email-wrapper .mail-list-container .mail-list .content .sender-name,
.email-wrapper .message-body .attachments-sections ul li .details p.file-name,
.settings-panel .chat-list .list .info p {
    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 100%;
    white-space: nowrap
}

.form-check .form-check-label {
    min-height: 18px;
    display: block;
    margin-left: 1.75rem;
    font-size: 0.875rem;
    line-height: 1.5
}

.form-check-label {
    margin-bottom: 0
}

.list-wrapper input[type="checkbox"] {
    margin-right: 15px
}

.form-check .form-check-label input {
    position: absolute;
    top: 0;
    left: 0;
    margin-left: 0;
    margin-top: 0;
    z-index: 1;
    cursor: pointer;
    opacity: 0;
    filter: alpha(opacity=0)
}

input[type="radio"],
input[type="checkbox"] {
    box-sizing: border-box;
    padding: 0
}

.list-wrapper ul li .form-check,
.list-wrapper ul li .form-check .form-check-label,
.email-wrapper .mail-sidebar .menu-bar .profile-list-item a .user .u-name,
.email-wrapper .mail-sidebar .menu-bar .profile-list-item a .user .u-designation,
.email-wrapper .mail-list-container .mail-list .content .sender-name,
.email-wrapper .message-body .attachments-sections ul li .details p.file-name,
.settings-panel .chat-list .list .info p {
    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 100%;
    white-space: nowrap
}

.form-check .form-check-label input[type="checkbox"]+.input-helper:before {
    content: "";
    width: 18px;
    height: 18px;
    border-radius: 2px;
    border: solid #405189;
    border-width: 2px;
    -webkit-transition: all;
    -moz-transition: all;
    -ms-transition: all;
    -o-transition: all;
    transition: all;
    transition-duration: 0s;
    -webkit-transition-duration: 250ms;
    transition-duration: 250ms
}

.form-check .form-check-label input[type="checkbox"]+.input-helper:before,
.form-check .form-check-label input[type="checkbox"]+.input-helper:after {
    position: absolute;
    top: 0;
    left: 0
}

.form-check .form-check-label input[type="checkbox"]+.input-helper:after {
    -webkit-transition: all;
    -moz-transition: all;
    -ms-transition: all;
    -o-transition: all;
    transition: all;
    transition-duration: 0s;
    -webkit-transition-duration: 250ms;
    transition-duration: 250ms;
    font-family: Material Design Icons;
    opacity: 0;
    filter: alpha(opacity=0);
    -webkit-transform: scale(0);
    -ms-transform: scale(0);
    -o-transform: scale(0);
    transform: scale(0);
    content: '\F12C';
    font-size: .9375rem;
    font-weight: bold;
    color: #ffffff
}

.form-check .form-check-label input[type="checkbox"]+.input-helper:before,
.form-check .form-check-label input[type="checkbox"]+.input-helper:after {
    position: absolute;
    top: 0;
    left: 0
}

.form-check .form-check-label input[type="checkbox"]:checked+.input-helper:before {
    background: #405189;
    border-width: 0
}

.form-check .form-check-label input[type="checkbox"]+.input-helper:before {
    content: "";
    width: 18px;
    height: 18px;
    border-radius: 2px;
    border: solid #405189;
    border-width: 2px;
    -webkit-transition: all;
    -moz-transition: all;
    -ms-transition: all;
    -o-transition: all;
    transition: all;
    transition-duration: 0s;
    -webkit-transition-duration: 250ms;
    transition-duration: 250ms
}

.form-check .form-check-label input[type="checkbox"]+.input-helper:after {
    font-family: FontAwesome;
    content: "\f095";
    display: inline-block;
    padding-right: 3px;
    vertical-align: middle;
    color: #fff
}

.text-primary,
.list-wrapper .completed .remove {
    color: #405189 !important
}

.list-wrapper .remove {
    cursor: pointer;
    font-size: 1.438rem;
    font-weight: 600;
    width: 1.25rem;
    height: 1.25rem;
    line-height: 20px;
    text-align: center
}

.ml-auto,
.list-wrapper .remove,
.mx-auto {
    margin-left: auto !important
}

.mdi-close-circle-outline:before {
    content: "\F15A"
}

.list-wrapper ul li {
    font-size: .9375rem;
    padding: .4rem 0;
    border-bottom: 1px solid #f3f3f3
}

.mdi:before {
    font-family: FontAwesome;
    content: "\f00d";
    display: inline-block;
    padding-right: 3px;
    vertical-align: middle;
    font-size: .756em;
    color: #405189
}

.list-wrapper ul {
    padding: 0;
    text-align: left;
    list-style: none;
    margin-bottom: 0
}

.flex-column-reverse {
    flex-direction: column-reverse !important
}

.d-flex,
.loader-demo-box,
.distribution-chart-legend .distribution-chart,
.distribution-chart-legend .distribution-chart .item,
.list-wrapper ul li,
.email-wrapper .mail-sidebar .menu-bar .profile-list-item a,
.email-wrapper .mail-sidebar .menu-bar .profile-list-item a .user,
.email-wrapper .mail-list-container .mail-list .details,
.email-wrapper .message-body .attachments-sections ul li .thumb,
.email-wrapper .message-body .attachments-sections ul li .details .buttons,
.lightGallery .image-tile .demo-gallery-poster,
.swal2-modal,
.navbar .navbar-menu-wrapper .navbar-nav,
.navbar .navbar-menu-wrapper .navbar-nav .nav-item.nav-profile,
.navbar .navbar-menu-wrapper .navbar-nav .nav-item.dropdown .navbar-dropdown .dropdown-item {
    display: flex !important
}
</style>
