function url(file) {
    return `/${(file ? file : "")}`
}
function defaultTabApp() {
    var tabapp = "tab-cerrar-sesion"
    return tabapp
}
function setTabApp(tabapp) {
    localStorage.setItem(TABAPPNAME, (tabapp ? tabapp : defaultTabApp()))
}
function getTabApp() {
    return localStorage.getItem(TABAPPNAME)
}
function removeTabApp() {
    localStorage.removeItem(TABAPPNAME)
}
function defaultNextTabApp() {
    var tabapp = "tab-notificaciones"
    return tabapp
}
function setNextTabApp(tabapp) {
    localStorage.setItem(NEXTTABAPPNAME, tabapp)
}
function getNextTabApp() {
    var tabapp = localStorage.getItem(NEXTTABAPPNAME)
    return tabapp
}
function removeNextTabApp() {
    localStorage.removeItem(NEXTTABAPPNAME)
}

var PRECACHENAME          = "RefugioAnimal-precache-v1"
var SYNCEVENTNAME         = "RefugioAnimal-sync-notifications"
var PERIODICSYNCEVENTNAME = "RefugioAnimal-periodic-sync-notifications"
var TABAPPNAME            = "tabRefugioAnimal"
var NEXTTABAPPNAME        = "nexttabRefugioAnimal"
