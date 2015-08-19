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
    else
        return "purple"
    //return "rgb("+r+","+g+","+b+")";
}
