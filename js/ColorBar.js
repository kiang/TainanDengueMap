function ColorBar(value) {
    if (value == 0)
        return "white"
    else if (value <= 2)
        return "green"
    else if (value <= 10)
        return "yellow"
    else if (value <= 20)
        return "orange"
    else if (value <= 50)
        return "red"
    else if (value <= 100)
        return "purple"
    else if (value <= 200)
        return "darkblue"
    else
        return "black"
    //return "rgb("+r+","+g+","+b+")";
}
