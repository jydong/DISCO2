library(shiny)
library(DT)


shinyServer(function(input, output, session){
  
  observe({
    # store the parsed query string 
    query <- parseQueryString(session$clientData$url_search)
  })
    
  # get user directory on the fangorns server
  # userDir does not change during the entire session
  username <- query$user
  userDir <- paste0("http://fangorn.colby.edu/disco2/users/",username)
  
  
  allOrigDataSets_url <- paste0("http://fangorn.colby.edu/disco2/users/alloriginal.php?user=",username)
  allDataSets_url <- paste0("http://fangorn.colby.edu/disco2/users/alldatasets.php?user=",username)
  
  df <- read.csv( allOrigDataSets_url, header=FALSE, stringsAsFactors = FALSE )
  allOrigDataSets <- c(c("None"), as.vector(unlist(df["V1"])))
  
  df <- read.csv( allDataSets_url, header=FALSE, stringsAsFactors = FALSE )
  allDataSets <- c("None")
  files <- as.vector(unlist(df["V1"]))
  for(name in files){
    allDataSets = c(allDataSets,substr(name,1,nchar(name)-4))
  }
  
  ############## Histogram ###############
  # update the list of select options for input$dataset_plot
  observe({
    updateSelectInput(session, "dataset_hist", choices=allDataSets,selected="None")
  })
  
  # reset input settings back to defaults when the data set used for histogram is changed
  observeEvent(input$dataset_hist,{
    updateSelectInput(session, "histVar", choices=headers_hist())
  })
  
  # return the original dataset name for a selected input$dataset_plot
  # e.g. "AustraliaCoast_pca_201707061322" returns "AustraliaCoast"
  #"poker_game_pca_201707061322" returns "poker_game"
  #"AustraliaCoast" returns "AustraliaCoast"
  origDataset_hist <- reactive({ 
    # if input$dataset_kmc itself is an original data set
    if(input$dataset_hist %in% allOrigDataSets){
      return (input$dataset_hist)
    }
    
    # if input$dataset_plot is an PCA data set, need to extract the name of its original data set 
    else{
      #split the input string by "_" and convert to a vector
      splited = unlist(strsplit(input$dataset_hist, "_")) 
      
      # remove "pca" and time substrings from the vector
      name = head(splited,-2) 
      
      # in case the dataset name contains "_" and has been seperated 
      if(length(name) > 1){
        name = paste(name, collapse = "_")
      }
      
      
      return(name)
    }
    
  })
  
  data_hist <- reactive({
    if(input$dataset_hist!="None"){ 
      read.csv(paste(userDir,"/",origDataset_hist(),"/dataset/",input$dataset_hist,".csv",
                             sep=""))
    }
  })
  
  headers_hist <- reactive({
    if(input$dataset_hist!="None"){
      if(input$dataset_hist %in% allOrigDataSets){
        # read the type file
        typeFile = read.csv(paste(userDir,"/",origDataset_hist(),"/",origDataset_hist(),
                                  "_type.csv", sep=""))
        
        numericHeaders <- vector(length=0)
        for(i in 1:length(names(typeFile))) {
          if(typeFile[1,i]=="number"){
            numericHeaders <- append(numericHeaders,names(typeFile)[i])
          }
        }
        return(numericHeaders)
      }
      else{
        return(names(data_hist()))
      }
    }
  
  })
  
  output$hist_fn <- renderPrint({
    if(input$dataset_hist!="None"){
      h4(input$dataset_hist,align = "center")
    }
  })
  
  output$hist <- renderPlot({
    # generate bins based on input$bins from ui.R

    if(input$dataset_hist == "None" || !input$histVar %in% headers_hist() ){
      return()
    }
    
    # draw the histogram with the specified number of bins
    if (!is.null(input$histVar) && nchar(input$histVar) > 0){
      x <- data_hist()[, input$histVar]
      bins <- seq(min(x), max(x), length.out = input$bins + 1) 
      hist(x, breaks = bins, col = 'lightblue', border = 'white',
           main= paste("Distribution of ", input$histVar))

    }
    
    else{
      return()
    }
  })
  
  
  ########### scatterplot ###########
  
  
  # update the list of select options for input$dataset_plot
  observe({
    updateSelectInput(session, "dataset_plot", choices=allDataSets,selected="None")
  })
  
  # reset input settings back to defaults when the data set used for histogram is changed
  observeEvent(input$dataset_plot,{
    updateSelectInput(session, "plotXVar", choices=headers_plot())
    updateSelectInput(session, "plotYVar", choices=headers_plot(), selected=headers_plot()[2])
    updateCheckboxInput(session, "eckert", value=FALSE)
    })
  
  origDataset_plot <- reactive({ 
    # if input$dataset_kmc itself is an original data set
    if(input$dataset_plot %in% allOrigDataSets){
      return (input$dataset_plot)
    }
    
    # if input$dataset_plot is an PCA data set, need to extract the name of its original data set 
    else{
      #split the input string by "_" and convert to a vector
      splited = unlist(strsplit(input$dataset_plot, "_")) 
      
      # remove "pca" and time substrings from the vector
      name = head(splited,-2) 
      
      # in case the dataset name contains "_" and has been seperated 
      if(length(name) > 1){
        name = paste(name, collapse = "_")
      }
      
      return(name)
    }
    
  })
  
  # read data for simple plotting
  # origDataset_plot() indicates the subdirectory that contains the data set,
  # input$dataset_plot indicates the data set name 
  data_plot <- reactive({
    if(input$dataset_plot!="None"){ 
      data <- read.csv(paste(userDir,"/",origDataset_plot(),"/dataset/",input$dataset_plot,".csv",
                             sep=""))
      origData <- read.csv(paste(userDir,"/",origDataset_plot(),"/dataset/",origDataset_plot(),".csv",
                                 sep=""))
      
      if(input$dataset_plot == origDataset_plot()){
        return(data)
      }
      else{
        return(cbind(data, origData))
      }
    }
  })
  
  headers_plot <- reactive({
    names(data_plot())
  })
  
  output$scatterplot_fn <- renderPrint({
    if(input$dataset_plot!="None"){
      h4(input$dataset_plot,align = "center")
    }
  })
  
  output$scatterplot <- renderPlot({
      if(input$plotXVar %in% headers_plot()==FALSE || 
         input$plotYVar %in% headers_plot()==FALSE){
        return()
      }
      if(!input$eckert){
        plot( data_plot()[, input$plotXVar], data_plot()[, input$plotYVar],
              xlab = input$plotXVar, ylab = input$plotYVar)
      }
      else{
        # grab longitude and latitude
        a <- data_plot()[, input$plotXVar]
        
        b <- data_plot()[, input$plotYVar]
        
        # convert Longitude from -180 to 180 to -4PI to 4PI
        rangeA <- range(a)
        
        print(rangeA)
        
        rangeB <- range(b)
        
        print(rangeB)
        
        centerline <- mean(rangeA)
        
        print(centerline)
        
        y <- b * 4.0 * pi / 180.0
        
        tmp <- b / 90.0
        
        x <- 2 * (1  + sqrt(1 - tmp*tmp)) * (a - centerline) * pi / 180.0
        
        print(x)
        print(y)
        
        plot(x, y)
        
        #readline(prompt="Enter to continue")
        
        #plot(a, b)
        
      }
  })
  
  output$plotInfo <- renderText({
    xy_str <- function(e) {
      if(is.null(e)) return("NULL\n")
      paste0(input$plotXVar, " = ", round(e$x, 1), "  ",
             input$plotYVar, " = ", round(e$y, 1), "\n")
    }
    paste("Hover: ", xy_str(input$plot_hover))
  })
  
  ############### View Data ##########
  
  # update the list of select options for input$dataset_plot
  observe({
    updateSelectInput(session, "dataset_view", choices=allDataSets,selected="None")
  })
  
  origDataset_view <- reactive({ 
    # if input$dataset_kmc itself is an original data set
    if(input$dataset_view %in% allOrigDataSets){
      return (input$dataset_view)
    }
    
    # if input$dataset_plot is an PCA data set, need to extract the name of its original data set 
    else{
      #split the input string by "_" and convert to a vector
      splited = unlist(strsplit(input$dataset_view, "_")) 
      
      # remove "pca" and time substrings from the vector
      name = head(splited,-2) 
      
      # in case the dataset name contains "_" and has been seperated 
      if(length(name) > 1){
        name = paste(name, collapse = "_")
      }
      
      return(name)
    }
    
  })
  
  data_view <- reactive({
    if(input$dataset_view!="None"){ 
      read.csv(paste(userDir,"/",origDataset_view(),"/dataset/",input$dataset_view,".csv",
                             sep="")) 
    }
  })
  
  output$viewData_fn <- renderPrint({
    if(input$dataset_view!="None"){
      h4(input$dataset_view,align = "center")
    }
  })
  
  observe({
    if(input$dataset_view!="None"){ 
      output$dataTable <- DT::renderDataTable(
        data_view(), options = list(searching = FALSE, scrollX = TRUE) )
    }
  })
  

  output$summary <- renderPrint({
    if(input$dataset_view!="None"){ 
      summary(data_view())
    }
  })
    
})
  
