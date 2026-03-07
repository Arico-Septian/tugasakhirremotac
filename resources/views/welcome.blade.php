<script>

function turnOn(id){

fetch("/ac/on/"+id)

}

function turnOff(id){

fetch("/ac/off/"+id)

}

</script>