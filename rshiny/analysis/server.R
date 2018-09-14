library(shiny)
library(DT)



shinyServer(function(input, output, session) {
  
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
  allPcaDataSets_url <- paste0("http://fangorn.colby.edu/disco2/users/allpcadatasets.php?user=",username)
  allSavedFiles_url <- paste0("http://fangorn.colby.edu/disco2/users/allanalysis.php?user=",username)
  
  
  df <- read.csv( allOrigDataSets_url, header=FALSE, stringsAsFactors = FALSE )
  allOrigDataSets <- c(c("None"), as.vector(unlist(df["V1"])))
  
  df <- read.csv( allDataSets_url, header=FALSE, stringsAsFactors = FALSE )
  allDataSets <- c("None")
  files <- as.vector(unlist(df["V1"]))
  for(name in files){
    allDataSets = c(allDataSets,substr(name,1,nchar(name)-4))
  }
  
  df <- read.csv( allPcaDataSets_url, header=FALSE, stringsAsFactors = FALSE )
  allPcaDataSets <- c("None")
  files <- as.vector(unlist(df["V1"]))
  for(name in files){
    allPcaDataSets = c(allPcaDataSets,substr(name,1,nchar(name)-4))
  }
  
  
  df <- read.csv( allSavedFiles_url, header=FALSE, stringsAsFactors = FALSE )
  allSavedFiles <- c("None")
  files <- as.vector(unlist(df["V1"])) 
  for(name in files){
    allSavedFiles = c(allSavedFiles,substr(name,1,nchar(name)-7))
  }
  
  # create a reactiveValues object with no values
  # store values that can be updated in multiple places: 
  values <- reactiveValues()
  
  
  
  ################ Plot Data ################
  
  # reset input settings back to defaults when the data set used for simple plotting is changed
  observeEvent(input$dataset_plot,{
    updateSelectInput(session, "plotX", choices=headers_plot())
    updateSelectInput(session, "plotY", choices=headers_plot(), selected=headers_plot()[2])
    updateCheckboxInput(session, "eckert", value=FALSE)
  })
  
  # update the list of select options for input$dataset_plot
  observe({
    updateSelectInput(session, "dataset_plot", choices=allDataSets,selected="None")
  })
  
  
  
  # return the original dataset name for a selected input$dataset_plot
  # e.g. "AustraliaCoast_pca_201707061322" returns "AustraliaCoast"
  #"poker_game_pca_201707061322" returns "poker_game"
  #"AustraliaCoast" returns "AustraliaCoast"
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
  # input$dataset_plot indicates name of the dataset
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
  
  
  
  # return a vector of original data headers 
  orig_headers_plot <- reactive({
    if(input$dataset_plot!="None"){
      
      # read the type file
      typeFile = read.csv(paste(userDir,"/",origDataset_plot(),"/",origDataset_plot(),
                                "_type.csv", sep=""))
      
      numericHeaders <- vector(length=0)
      for(i in 1:length(names(typeFile))) {
        if(typeFile[1,i]=="number"){
          numericHeaders <- append(numericHeaders,names(typeFile)[i])
        }
      }
      return(numericHeaders)
    }
    return(NULL)
  })
  
  # return a list of numeric headers of the data set used for simple plotting
  headers_plot <- reactive({
    if(input$dataset_plot!="None"){
      
      # if the selected data set is not a pca dataset, read the type file
      if(input$dataset_plot %in% allOrigDataSets){
       
        return(orig_headers_plot())
      }
      
      # if the selected data set is a pca data set 
      else{
        dataFile = read.csv(paste(userDir,"/",origDataset_plot(),"/dataset/",input$dataset_plot,
                                  ".csv", sep=""))
        pcaDims <- names(dataFile)
        
        return(c(pcaDims, orig_headers_plot()))
      }
      
      
    }
    return(NULL) #if no dataset has not been selected, return NULL
  })
  
  output$simple_plot_fn <- renderPrint({
    if(input$dataset_plot!="None"){
      h4(input$dataset_plot,align = "center")
    }
  })
  
  output$simple_plot <- renderPlot({
    if(input$plotX %in% headers_plot()==FALSE || 
       input$plotY %in% headers_plot()==FALSE){
      return()
    }
    if(!input$eckert){
      plot( data_plot()[, input$plotX], data_plot()[, input$plotY],
          xlab = input$plotX, ylab = input$plotY)
    }
    else{
      # grab longitude and latitude
      a <- data_plot()[, input$plotX]
      
      b <- data_plot()[, input$plotY]
      
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
  
  output$simple_plot_pts <- renderText({
    xy_str <- function(e) {
      if(is.null(e)) return("NULL\n")
      paste0(input$plotX, " = ", round(e$x, 1), "  ",
             input$plotY, " = ", round(e$y, 1), "\n")
    }
    paste("Hover: ", xy_str(input$plot_hover))
  })
  
  
  
  ################# K-means Clustering #################
  
  # add a list of dataset choices to "dataset_km" selectInput 
  observe({
    updateSelectInput(session, "dataset_kmc", choices=allDataSets,selected="None")
  })
  
  # reset input settings back to defaults when the dataset used for kmeans clustering is changed
  observeEvent(input$dataset_kmc,{
    updateSelectInput(session, "plotX_kmc", choices=headers_kmc())
    updateSelectInput(session, "plotY_kmc", choices=headers_kmc(), selected=headers_kmc()[2])
    updateSelectInput(session, "dims", choices=headers_kmc()) 
    updateNumericInput(session, "clusters", value=3) 
    updateNumericInput(session, "iter", value=10) 
    
    values$kmeansData <- NULL
    values$results_kmc <- NULL
  })
  
  # reset inputs when the reset button is clicked
  observeEvent(input$reset_kmc,{
    updateSelectInput(session, "plotX_kmc", choices=headers_kmc())
    updateSelectInput(session, "plotY_kmc", choices=headers_kmc(), selected=headers_kmc()[2])
    updateSelectInput(session, "dims", choices=headers_kmc()) 
    updateNumericInput(session, "clusters", value=3) 
    updateNumericInput(session, "iter", value=10) 
  })
  
  # return the original dataset name for a selected input$dataset_kmc
  # e.g. "AustraliaCoast_pca_201707061322" returns "AustraliaCoast"
  #"poker_game_pca_201707061322" returns "poker_game"
  #"AustraliaCoast" returns "AustraliaCoast"
  origDataset_kmc <- reactive({ 
    # if input$dataset_kmc itself is an original data set
    if(input$dataset_kmc %in% allOrigDataSets){
      return (input$dataset_kmc)
    }
    
    # if input$dataset_kmc is an PCA data set, need to extract the name of its original data set 
    else{
      #split the input string by "_" and convert to a vector
      splited = unlist(strsplit(input$dataset_kmc, "_")) 
      
      # remove "pca" and time substrings from the vector
      name = head(splited,-2) 
      
      # in case the dataset name contains "_" and has been seperated 
      if(length(name) > 1){
        name = paste(name, collapse = "_")
      }

      
      return(name)
    }
    
  })
  

  # read data for k-means clustering 
  # origDataset_kmc() indicates the subdirectory that contains the dataset,
  # input$dataset_kmc indicates name of the dataset 
  data_kmc <- reactive({
    if(input$dataset_kmc!="None"){ 
      read.csv(paste(userDir,"/",origDataset_kmc(),"/dataset/",input$dataset_kmc,".csv",
                     sep=""))
    }
  })
  
  # return a list of numeric headers of the data set used for k-means clustering 
  headers_kmc <- reactive({
    if(input$dataset_kmc!="None"){
      
      # if the selected data set is not a pca dataset, read the type file
      if(input$dataset_kmc %in% allOrigDataSets){
      
        # read the type file
        typeFile = read.csv(paste(userDir,"/",origDataset_kmc(),"/",origDataset_kmc(),
                                  "_type.csv", sep=""))
        
        numericHeaders <- vector(length=0)
        for(i in 1:length(names(typeFile))) {
          if(typeFile[1,i]=="number"){
            numericHeaders <- append(numericHeaders,names(typeFile)[i])
          }
        }
        return(numericHeaders)
      }
      
      # if the selected data set is a pca data set 
      else{
        dataFile = read.csv(paste(userDir,"/",origDataset_kmc(),"/dataset/",input$dataset_kmc,
                                  ".csv", sep=""))
        return(names(dataFile))
      }
      
  
    }
    else{    #if no dataset has not been selected, return an empty vector
      return(vector(length=0))
    }
  })

  
  output$kmc_fn <- renderPrint({
    if(input$dataset_kmc!="None"){
      h4(input$dataset_kmc,align = "center")
    }
  })
  
  cluster_names <- function(centers){
    dims_names <- colnames(centers)
    K <- nrow(centers) # number of clusters
    
    return_names <- vector(length = K)
    
    # Create a K by K matrix M, initialize it to all -1 values
    M <- matrix(-1, K, K)
    
    dims_sd <- apply(centers, 2, sd) # sd of each dimension
    
    # compare each pair of cluster means  
    for(i in 1:K){
      for(j in 1:K){
        if(i != j){
          # d is the largest meaningful difference between mean i and mean j 
          d <- -1
          for(k in 1:length(dims_names)){
            diff <- abs(centers[i, k] - centers[j, k]) / dims_sd[k]
            
            if(diff >= d){
              d <- diff 
              M[i, j] <- k
            }
          }
        }
      }
    }
    
    A <- vector(length = K)
    B <- vector(length = K)
    C <- vector(length = K)
    
    # adjectives for dimensions (low, average, high)
    A_adj <- vector(length = K)
    B_adj <- vector(length = K)
    C_adj <- vector(length = K)
    
    for(i in 1:nrow(M)){
      row <- table(M[i, ]) 
      sorted_dims <- names(sort(row, decreasing = TRUE))
      sorted_dims <- sorted_dims[sorted_dims >= 1]
      
      A[i] <- sorted_dims[1] # the most common dimension value in row i that is >= 1
      
      if(length(sorted_dims) > 1){
        B[i] <- sorted_dims[2] # the second most common dimension value in row i that is >= 1
        
        if(length(sorted_dims) > 2){
          C[i] <- sorted_dims[3] # the third most common dimension value in row i that is >= 1
        }
        else{
          C[i] <- -1
        }
      }
      else{
        B[i] <- -1
      }
    
      col <- as.numeric(A[i])
      A_data_min <- min(centers[,col])
      A_data_max <- max(centers[,col])
      A_data_range <- A_data_max - A_data_min
      one_third <- A_data_min + A_data_range*1/3
      two_third <- A_data_min + A_data_range*2/3
      if(centers[i, col] <= one_third){
        A_adj[i] <- "low" #‘low’ if u_A_i is in the bottom third of the values of column A_i
      }
      else if( one_third < centers[i, col] && centers[i, col] <= two_third){
        A_adj[i] <- "avg" # “avg’ if u_A is in the middle third of the values of column A_i
      }
      else{
        A_adj[i] <- "high" # “high” if u_A is in the top third of the values of column A_i
      }
      
      if(B[i] >= 1){
        col <- as.numeric(B[i])
        B_data_min <- min(centers[, col])
        B_data_max <- max(centers[, col])
        B_data_range <- B_data_max - B_data_min
        one_third <- B_data_min + B_data_range*1/3
        two_third <- B_data_min + B_data_range*2/3
        if(centers[i, col] <= one_third){
          B_adj[i] <- "low"
        }
        else if( one_third < centers[i, col] && centers[i, col] <= two_third){
          B_adj[i] <- "avg"
        }
        else{
          B_adj[i] <- "high"
        }
      }
      
      if(C[i] >= 1){
        col <- as.numeric(C[i])
        C_data_min <- min(centers[, col])
        C_data_max <- max(centers[, col])
        C_data_range <- C_data_max - C_data_min
        one_third <- C_data_min + C_data_range*1/3
        two_third <- C_data_min + C_data_range*2/3
        if(centers[i, col] <= one_third){
          C_adj[i] <- "low"
        }
        else if( one_third < centers[i, col] && centers[i, col] <= two_third){
          C_adj[i] <- "avg"
        }
        else{
          C_adj[i] <- "high"
        }
      }
      
      title <- paste0(A_adj[i] , "_",dims_names[as.numeric(A[i])])
      
      if(B[i] >= 1){
        title <- paste0(title , "_", B_adj[i], "_", dims_names[as.numeric(B[i])])
      }
      
      if(C[i] >= 1){
        title <- paste0(title , "_", C_adj[i], "_", dims_names[as.numeric(C[i])])
      }
      return_names[i] <- title
    }
    
    return(return_names)
    
  } 
  


  # plot k-means clustering output
  observeEvent(input$ok_kmc,{
    if(is.null(input$dims)|| nchar(input$dims) < 1){
      return()
    }
    
    values$kmeansData <- data_kmc()[, input$dims]
    values$results_kmc <- kmeans(values$kmeansData, input$clusters, input$iter)
    
    
    palette("default") # use default palette
    
    output$kmeans <- renderPlot({
      
      # return if current input$kmeansPlotX or input$kmeansPlotY is not in headers_kmc()
      # sometimes values for these two inputs do not get updated immediately when data set 
      # is changed
      if(input$plotX_kmc %in% headers_kmc()==FALSE || 
         input$plotY_kmc %in% headers_kmc()==FALSE){
        return()
      }
      
      par(mar = c(5.1, 4.1, 0, 1)) # specify margins
  
      xvar = data_kmc()[,input$plotX_kmc]
      yvar = data_kmc()[,input$plotY_kmc]
      
      
      plot(xvar, yvar, col = values$results_kmc$cluster, xlab=input$plotX_kmc,
           ylab=input$plotY_kmc, pch = 20, cex = 2)
    })
    
    
    # format a table for displaying cluster means and sizes
    if(is.null(values$results_kmc)){
      return()
    }
    
    centers = round(values$results_kmc$center,4)
    
    cluster_names <- cluster_names(centers)
    
    if(is.null(colnames(centers))){
      colnames(centers) <- isolate(input$dims)
    }
    table <- cbind(cluster_names, centers)
    table <- cbind(table, values$results_kmc$size)
    colnames(table) <- c("Cluster Name", colnames(centers), "Cluster Size")
    
    # create a palette for kmeans_table, since green in palette() is "green3", replace it with "green"
    mypalette <- palette()
    mypalette[3] <- "green" #"green3" is not defined in styleEqual
    

    kmeans_table <- datatable(table,
                              caption='Table 1: Cluster Mean and Size.',
                              options = list(paging=FALSE, searching = FALSE)) %>% formatStyle(
      column = 0,
      color = styleEqual(1:isolate(input$clusters),  mypalette[1: isolate(input$clusters)])
    )
    
    output$kmeans_centers <- DT::renderDataTable(kmeans_table)
    

  })
  
  
    
  # save an k-means clustering analysis 
  observeEvent(input$save_kmc,{
    
    results = values$results_kmc
 
    if(!is.null(results)){
      showModal(nameKmcModal())
    }
  })
  
  # create a modal(dialog box) to allow the user to enter a customized name
  nameKmcModal <- function(failed = FALSE) {
    modalDialog(
      textInput("name_kmc", paste0("Name Your Analysis: ",input$dataset_kmc,
                "_cluster_"), placeholder = 'Type in a name or use current time as name '),
      span("The following symbols are not allowed: "),
      tags$br(),
      span("\ / : * ? | < > _ space"),
      if (failed)
        div(tags$b("The name doesn't have the right format or it already exists.", style = "color: red;")),
      
      footer = tagList(
        modalButton("Cancel"),
        actionButton("name_kmc_cancel", "Use Current Time as Name"),
        actionButton("name_kmc_ok", "OK")
      ) 
    )
  }
  
  # name the analysis with user input name
  # only allow the user to name the last part of the filename 
  # "AustraliaCoast_cluster_[named by user]"
  observeEvent(input$name_kmc_ok, {
    # Check if user input is not empty
    if (!is.null(input$name_kmc) && nzchar(input$name_kmc)) {
      # check if user input is valid
      checked <- gsub("[\\<>:|?*_/[:space:]]", "." ,input$name_kmc)
      if(checked != input$name_kmc){
        print("not matched")
        showModal(nameKmcModal(failed = TRUE))
        return()
      }
      
      # check if filename already exists 
      fn <- paste0(input$dataset_kmc,"_cluster_",input$name_kmc)
      
      if(fn %in% allSavedFiles){
        showModal(nameKmcModal(failed = TRUE))
      }
      else{
       
        print(paste0(userDir,"/",origDataset_kmc(),"/analysis/",input$dataset_kmc,
                     "_cluster_",input$name_kmc,"_id.csv")) 
        
        results = values$results_kmc
        
        # save ids
        # write results()$cluster to a csv file
        idFile = paste0(userDir,"/",origDataset_kmc(),"/analysis/",input$dataset_kmc,
                       "_cluster_",input$name_kmc,"_id.csv")
        write.table(results$cluster, idFile, sep=",", row.names=FALSE)

        # save means
        meanFile = paste(userDir,"/",origDataset_kmc(),"/analysis/",input$dataset_kmc,
                         "_cluster_",input$name_kmc,"_mean.csv", sep="")
        table <- cbind(results$center, results$size)
        colnames(table) <- c(colnames(results$center), "Cluster Size")

        write.table(table, meanFile, sep=",", row.names=FALSE)

        # add this filename to allSavedFiles and update relevent selectInputs
        name <- paste0(input$dataset_kmc,"_cluster_",input$name_kmc)
        allSavedFiles <- c(allSavedFiles, name )
        updateSelectInput(session, "dataset_plot", choices=allSavedFiles)
        updateSelectInput(session, "fn_kmc", choices=allSavedFiles)
        
        removeModal()

        # show a success message
        showModal(modalDialog( title = "K-means Clustering Analysis",
                               paste0("This analysis has been saved as ", name),
                               footer = modalButton("Ok")
                               ))
      
      }
    }
  })
  
  # name the kmc analysis with current time 
  # "AustraliaCoast_cluster_YYYYMMDDHHMM"
  observeEvent(input$name_kmc_cancel, {
    removeModal() 
    
    # generate a string of current time in the format of YYYYMMDDHHMM
    # e.g. 201707061152
    time <- format(Sys.time(), "%Y%m%d%H%M")
    print(paste0(userDir,"/",origDataset_kmc(),"/analysis/",input$dataset_kmc,
                 "_cluster_",time,"_id.csv"))
    
    results = values$results_kmc
    
    # save ids
    # write results()$cluster to a csv file
    idFile = paste0(userDir,"/",origDataset_kmc(),"/analysis/",input$dataset_kmc,
                    "_cluster_",time,"_id.csv")
    write.table(results$cluster, idFile, sep=",", row.names=FALSE)
    
    # save means
    meanFile = paste(userDir,"/",origDataset_kmc(),"/analysis/",input$dataset_kmc,
                     "_cluster_",time,"_mean.csv", sep="")
    table <- cbind(results$center, results$size)
    colnames(table) <- c(colnames(results$center), "Cluster Size")
    
    write.table(table, meanFile, sep=",", row.names=FALSE)
    
    # add this filename to allSavedFiles and update relevent selectInputs
    name <- paste0(input$dataset_kmc,"_cluster_",time)
    allSavedFiles <- c(allSavedFiles, name )
    updateSelectInput(session, "dataset_plot", choices=allSavedFiles)
    updateSelectInput(session, "fn_kmc", choices=allSavedFiles)
    
    
    # show a success message
    showModal(modalDialog( title = "K-means Clustering Analysis",
                           paste0("This analysis has been saved as ", name),
                           footer = modalButton("Ok")
    ))
    
  })

  
  ################ Principle Component Analysis ########
  
  # add a list of dataset options to input$dataset_pca
  observe({
    updateSelectInput(session, "dataset_pca", choices=allOrigDataSets,selected="None")
  })
  
  # reset settings and reactive values related to pca back to defaults when the data set is changed
  observeEvent(input$dataset_pca,{
    updateSelectInput(session, "dims_pca", choices=headers_pca()) 
    updateCheckboxInput(session, "normalize", value=TRUE)
    values$pca_eigen_table  <- NULL      
    values$pca_projected_data <- NULL
    values$pca_results <- NULL
  })
  
  # reset inputs when the reset button is clicked
  observeEvent(input$reset_pca,{
    updateSelectInput(session, "dims_pca", choices=headers_pca()) 
    updateCheckboxInput(session, "normalize", value=TRUE)
  })
  
  # read data for pca
  data_pca <- reactive({
    if(input$dataset_pca!="None"){ 
      read.csv(paste(userDir,"/",input$dataset_pca,"/dataset/",input$dataset_pca,".csv",
                     sep=""))
    }
    else{
      return(NULL)
    }
  })
  
  
  # return a list of numeric headers of the data set used for pca
  headers_pca <- reactive({
    if(input$dataset_pca!="None"){
      # read the type file
      typeFile = read.csv(paste(userDir,"/",input$dataset_pca,"/",input$dataset_pca,
                                "_type.csv", sep=""))
      
      numericHeaders <- vector(length=0)
      for(i in 1:length(names(typeFile))) {
        if(typeFile[1,i]=="number"){
          numericHeaders <- append(numericHeaders,names(typeFile)[i])
        }
      }
      return(numericHeaders)
    }
    else{    #if no dataset has not been selected, return NULL
      return(NULL)
    }
  })
  
  numeric_data_pca <- reactive({
    if(!is.null(data_pca()) && !is.null(headers_pca())){
      return(data_pca()[headers_pca()])
    }
    return(NULL)
  })
  
  # return a list of min, max and range of the columns of the numeric data 
  data_summary_pca <- reactive({
    if(!is.null(numeric_data_pca())){
      min <- apply(numeric_data_pca(),2,min,na.rm=TRUE)
      min <- as.vector(min) 
      max <- apply(numeric_data_pca(),2,max,na.rm=TRUE)
      max <- as.vector(max)
      range = max - min
      mean <- apply(numeric_data_pca(),2,mean,na.rm=TRUE)
      mean <- as.vector(mean)
      
      return(list(min=min, max=max, range=range, mean=mean))
    }
    return(NULL)
  })
  
  # normalize a data frame
  # subtract each column by its min, then divide it by its range
  normalize <- function(data){
    
    result <- data # make a copy of the input data frame
    min <- data_summary_pca()$min
    range <- data_summary_pca()$range
    
    # operate on each column
    for(i in 1:length(names(result))){
      col <- names(result)[i]
      result[,col] <- (result[,col] - min[i]) / range[i]
    }
    
    return(result)
  }
  
  
  # return pca results
  pca_results <- eventReactive(input$ok_pca,{
    if(input$dataset_pca!="None"){
      if(!is.null(input$dims_pca) && nchar(input$dims_pca) > 0){
          
          if(input$normalize){
            data = normalize(numeric_data_pca())
          }
          else{
            data = numeric_data_pca()
          }
          data_subset <- na.omit(data[, input$dims_pca])
          
          pca_output <- prcomp(na.omit(data_subset), 
                               center = TRUE, scale. = FALSE, retx=TRUE)
          return(pca_output)
      
      }
      else{
        return(NULL)
      }
    }
    else{
      return(NULL)
    }
  })
  

  # return a vector of eigenvalues
  pca_eigenVals <- eventReactive(input$ok_pca,{
    pca_results()$sdev^2
  })
  
  # return a vector of cumulative proportions explanied by eigenvectors
  pca_eigenVals_cum_props <- eventReactive(input$ok_pca,{
    cumsum(pca_eigenVals()/sum(pca_eigenVals()))
  })
  
  # return a summary table of eigenvalues and eigenvectors 
  pca_eigen_table <- eventReactive(input$ok_pca,{
    if(!is.null(pca_results()) && dim(pca_results()$rotation)!=1){
      # transpose and round pca_results()$rotation
      eigVecs <- round(t(pca_results()$rotation),4)
      eigVecs_names <- colnames(pca_results()$x)
    
      table <- cbind(round(pca_eigenVals(),4), round(pca_eigenVals_cum_props(),4))
      table <- cbind(table, eigVecs) 
      table <- cbind(eigVecs_names, table)
      
      # add a row of original column means
      means <- round(apply(numeric_data_pca(),2,mean)[input$dims_pca],4)
      means <- as.vector(means)
      means <- c(c(NA,NA,"Mean"), means)
      table <- rbind(table, means)
      
      # add a row of original column sd
      sd <- round(apply(numeric_data_pca(),2,sd)[input$dims_pca],4)
      sd <- as.vector(sd)
      sd <- c(c(NA,NA,"SD"), sd)
      table <- rbind(table, sd)
    
      colnames(table) <- c(c("E-vec", "E-val","Cumulative"), colnames(eigVecs))
      
      return(table)
    }
    else{
      return(NULL)
    }
  })
  
  # return a matrix of pca projected data
  pca_projected_data <- eventReactive(input$ok_pca,{
    if(!is.null(pca_results()) ){
      return(pca_results()$x)
    }
    else{
      return(NULL)
    }
  })
  
  output$pca_fn <- renderPrint({
    if(input$dataset_pca!="None"){
      h4(input$dataset_pca,align = "center")
    }
  })
  
  # disply tables and graphs if input$ok_pca is clicked
  observeEvent(input$ok_pca,{
    
    values$pca_eigen_table <- isolate(pca_eigen_table())
    values$pca_projected_data <- isolate(pca_projected_data())
    values$pca_results <- isolate(pca_results())
    
    output$pca_eig <- renderTable(values$pca_eigen_table, na="", digits=4)   
    

    output$pca_table <- DT::renderDataTable(
      round(values$pca_projected_data,4),caption = 'Projected PCA Data', options = list(searching = FALSE) 
    )
    
    output$pca_plots <-renderPlot({
      x <- values$pca_results
      if(is.null(x)){
        return(NULL)
      }
      x.var <- x$sdev ^ 2
      x.pvar <- x.var/sum(x.var)
      print("proportions of variance:")
      print(x.pvar)

      par(mfrow=c(2,2))
      barplot(x.pvar,xlab="Principal Component", ylab="Proportion of Variance Explained", ylim=c(0,1))
      plot(cumsum(x.pvar),xlab="Principal Component", ylab="Cumulative Proportion of Variance Explained", ylim=c(0,1))
      par(mfrow=c(1,1))
    })
  })
  
  # save a PCA
  observeEvent(input$save_pca,{
  
    if(!is.null(pca_projected_data())){
      showModal(namePcaModal())
    }
  })
  
  # create a modal(dialog box) to allow the user to enter a customized name
  namePcaModal <- function(failed = FALSE) {
    modalDialog(
      textInput("name_pca", paste0("Name Your Analysis: ",input$dataset_pca,
                                   "_pca_"), placeholder = 'Type in a name or use current time as name '),
      span("The following symbols are not allowed: "),
      tags$br(),
      span("\ / : * ? | < > _ space"),
      if (failed)
        div(tags$b("The name doesn't have the right format or it already exists.", style = "color: red;")),
      
      footer = tagList(
        modalButton("Cancel"),
        actionButton("name_pca_cancel", "Use Current Time as Name"),
        actionButton("name_pca_ok", "OK")
      ) 
    )
  }
  
  # name the analysis with user input name
  # only allow the user to name the last part of the filename 
  # "AustraliaCoast_pca_[named by user]"
  observeEvent(input$name_pca_ok, {
    # make sure user input is not empty
    if (!is.null(input$name_pca) && nzchar(input$name_pca)) {
      # check if user input is valid
      checked <- gsub("[\\<>:|?*_/[:space:]]", "." ,input$name_pca)
      if(checked != input$name_pca){
        showModal(namePcaModal(failed = TRUE))
        return()
      }
      
      # check if filename already exists 
      fn <- paste0(input$dataset_pca,"_pca_",input$name_pca)
      
      if(fn %in% allSavedFiles){
        showModal(namePcaModal(failed = TRUE))
      }
      else{
        
        # save projected data
        dataFile = paste(userDir,"/",input$dataset_pca,"/dataset/",input$dataset_pca,
                         "_pca_",input$name_pca,".csv", sep="")
        write.table(pca_projected_data(), dataFile, sep=",", row.names=FALSE)
        
        # save eigenvalues, proportions, eigenvectors, means and sd of the original data
        anlysFile = paste(userDir,"/",input$dataset_pca,"/analysis/",input$dataset_pca,
                          "_pca_",input$name_pca, "_anlys.csv", sep="")
        
        write.table(pca_eigen_table(), anlysFile, sep=",", row.names=FALSE)
        
        # add this pca dataset name to allDataSets and allPcaDataSets
        # update the dropdown list for input$dataset_kmc
        name <- paste0(input$dataset_pca,"_pca_",input$name_pca)
        allDataSets <- c(allDataSets, name )
        allPcaDataSets <- c(allPcaDataSets, name )
        
        updateSelectInput(session, "dataset_kmc", choices=allDataSets)
        updateSelectInput(session, "fn_pca", choices=allPcaDataSets)
        
        removeModal()
        
        # show a success message
        showModal(modalDialog( title = "Principle Component Analysis",
                               paste0("This analysis has been saved as ", name, "."),
                               footer = modalButton("Ok")
        ))
      }
    }
  })
  
  # name the kmc analysis with current time 
  # "AustraliaCoast_cluster_YYYYMMDDHHMM"
  observeEvent(input$name_pca_cancel, {
    removeModal() 
    
    # generate a string of current time in the format of YYYYMMDDHHMM
    # e.g. 201707061152
    time <- format(Sys.time(), "%Y%m%d%H%M")
    
    # save projected data
    dataFile = paste(userDir,"/",input$dataset_pca,"/dataset/",input$dataset_pca,
                     "_pca_",time,".csv", sep="")
    write.table(pca_projected_data(), dataFile, sep=",", row.names=FALSE)
    
    # save eigenvalues, proportions, eigenvectors, means and sd of the original data
    anlysFile = paste(userDir,"/",input$dataset_pca,"/analysis/",input$dataset_pca,
                      "_pca_",time, "_anlys.csv", sep="")
    
    write.table(pca_eigen_table(), anlysFile, sep=",", row.names=FALSE)
    
    # add this pca dataset name to allDataSets and allPcaDataSets
    # update the dropdown list for input$dataset_kmc
    name <- paste0(input$dataset_pca,"_pca_",time)
    allDataSets <- c(allDataSets, name )
    allPcaDataSets <- c(allPcaDataSets, name )
    
    updateSelectInput(session, "dataset_kmc", choices=allDataSets)
    updateSelectInput(session, "fn_pca", choices=allPcaDataSets)
    
    removeModal()
    
    # show a success message
    showModal(modalDialog( title = "Principle Component Analysis",
                           paste0("This analysis has been saved as ", name,"."),
                           footer = modalButton("Ok")
    ))
  })
  

  
  
  
  ############# View Analysis #################
  
  # add a list of filename choices to "filename" selectInput on the "View Analysis" page
  observe({
    updateSelectInput(session, "fn_kmc", choices=allSavedFiles,selected="None")
    updateSelectInput(session, "fn_pca", choices=allPcaDataSets,selected="None")
  })
  
  # update select options for input$plotX_va and input$plotY_va if input$fn_kmc changes
  observeEvent(input$fn_kmc,{
    updateSelectInput(session, "plotX_va", choices=headers_view_kmc())
    updateSelectInput(session, "plotY_va", choices=headers_view_kmc(), selected=headers_view_kmc()[2])
  })
  
  # set input$fn_pca to "None" if input$fn_kmc is not "None
  observe({
    if(input$fn_kmc!= "None"){
      updateSelectInput(session, "fn_pca", selected="None")
    }
  })
  
  # set input$fn_kmc to "None" if input$fn_pca is not "None
  observe({
    if(input$fn_pca!= "None"){
      updateSelectInput(session, "fn_kmc", selected="None")
    }
  })
  
  # read data for viewing a previously saved analysis
  data_view_kmc <- reactive({
    if(!is.null(input$fn_kmc) && nchar(input$fn_kmc)>0 && input$fn_kmc!="None"){ 
      dataset = strsplit(input$fn_kmc, "_")[[1]][[1]]
      
      read.csv(paste(userDir,"/",dataset,"/dataset/",dataset,".csv",
                     sep=""))
    }
    
  })
  
  data_view_pca <- reactive({
    if(!is.null(input$fn_pca) && nchar(input$fn_pca)>0 && input$fn_pca!="None"){ 
      dataset = input$fn_pca
      read.csv(paste(userDir,"/",dataset,"/dataset/",dataset,".csv",
                     sep=""))
      #read.csv("http://cs.colby.edu/maxwell/iris_all.csv")
    }
  })
  
  
  # return a list of numeric headers of the data set used for viewing a previously saved analysis
  headers_view_kmc <- reactive({
    if(!is.null(input$fn_kmc) && nchar(input$fn_kmc)>0 && input$fn_kmc!="None"){ 
      dataset = strsplit(input$fn_kmc, "_")[[1]][[1]]
      typeFile = read.csv(paste(userDir,"/",dataset,"/",dataset,
                                "_type.csv", sep=""))
      
      numericHeaders <- vector(length=0)
      for(i in 1:length(names(typeFile))) {
        if(typeFile[1,i]=="number"){
          numericHeaders <- append(numericHeaders,names(typeFile)[i])
        }
      }
      return(numericHeaders)
    }
    else{ #if no dataset has not been selected, return NULL
      return(NULL)
    }
  })

  
  # display filename of the selected analysis
  output$viewSaved_fn <- renderPrint({
    if(input$fn_kmc!="None"){
      h4(input$fn_kmc,align = "center")
    }
    else{
      if(input$fn_pca!="None"){
        h4(input$fn_pca,align = "center")
      }
    }
  })

  observe({
    if(input$fn_kmc!="None"){ 

      # display a scatterplot of clustered data
      output$viewSaved_kmc <- renderPlot({
        
        if(input$plotX_va %in% headers_view_kmc()==FALSE || 
           input$plotY_va %in% headers_view_kmc()==FALSE){
          return()
        }
      
        dataset = strsplit(input$fn_kmc, "_")[[1]][[1]]
        idFile =  paste(userDir,"/",dataset,"/analysis/",input$fn_kmc,"_id.csv", sep="")
        cluster = unlist(read.csv(idFile))
    
        plot(data_view_kmc()[,input$plotX_va], data_view_kmc()[,input$plotY_va],
             col = cluster, xlab=input$plotX_va,
             ylab=input$plotY_va, pch = 20, cex = 2)
      })
      
      
      output$viewSaved_kmc_centers <- DT::renderDataTable({
        if(input$fn_kmc == "None"){
          return()
        }
        
        dataset <- strsplit(input$fn_kmc, "_")[[1]][[1]]
        meanFile <-  paste(userDir,"/",dataset,"/analysis/",input$fn_kmc,"_mean.csv", sep="")
        centers <- round(read.csv(meanFile),4)
        numClusters <- nrow(centers)
        
        # create a palette for kmeans_table, since green in palette() is "green3", replace it with "green"
        mypalette <- palette()
        mypalette[3] <- "green" #"green3" is not defined in styleEqual
        
        kmeans_table <- datatable(centers,
                                  caption='Table 1: Cluster Mean and Size.',
                                  options = list(paging=FALSE, searching = FALSE)) %>% formatStyle(
          column = 0,
          color = styleEqual(1:isolate(numClusters),  mypalette[1: isolate(numClusters)])
        )
        return(kmeans_table)
      })
    }
  })
  
  observe({
    if(input$fn_pca!="None"){ 
      
      origDataset <- strsplit(input$fn_pca, "_")[[1]][[1]]
      tableFile <- read.csv(paste0(userDir,"/",origDataset,"/analysis/",input$fn_pca,"_anlys.csv"))
      
      output$viewSaved_pca_eig <- renderTable(tableFile, na="", digits=4)
      
      output$viewSaved_pca_plots <-renderPlot({
        var <- as.vector(apply(tableFile["E.val"],2,sqrt))
        var <- head(var,-2) 
        pvar <- var/sum(var)
        print("proportions of variance:")
        print(pvar)
        
        par(mfrow=c(2,2))
        barplot(pvar,xlab="Principal Component", ylab="Proportion of Variance Explained", ylim=c(0,1))
        plot(cumsum(pvar),xlab="Principal Component", ylab="Cumulative Proportion of Variance Explained", ylim=c(0,1))
        par(mfrow=c(1,1))
      })
      
      dataFile <- round(read.csv(paste0(userDir,"/",origDataset,"/dataset/",input$fn_pca,".csv")),4)
      output$viewSaved_pca_table <- DT::renderDataTable(
        dataFile,caption = 'Projected PCA Data', options = list(searching = FALSE,scrollX = TRUE) 
      )
    }
  })
})
