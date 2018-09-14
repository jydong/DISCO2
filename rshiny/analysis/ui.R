library(shiny)


shinyUI(fluidPage(
  # Application title
  #titlePanel(h1("DISCO2 - ANALYSIS")),

  navbarPage("Analysis",
             
    tabPanel("Plot Data",
      sidebarLayout(
        sidebarPanel(
          h3("Plot Data"),
          selectInput("dataset_plot", label = h5("Choose a Data Set"),
            choices = c("None")),

          # Display this only if the filename input is not None 
          conditionalPanel(condition = "input.dataset_plot != 'None' " ,
          selectInput("plotX", label = h5("Variable for X-axis"),
                     choices = NULL),
          selectInput("plotY", label = h5("Variable for Y-axis"),
                     choices = NULL),
          checkboxInput("eckert", h5("Eckert Projection"),value=FALSE)
          )
        ),

        mainPanel(
          verbatimTextOutput("urlText"),
          htmlOutput("simple_plot_fn"),
          plotOutput("simple_plot",hover = "plot_hover"),
          conditionalPanel(condition = "input.dataset_plot != 'None' ",
            verbatimTextOutput("simple_plot_pts")
          )
        )
      )
    ),         
    tabPanel("K-means Clustering",
      sidebarLayout(
       sidebarPanel(
         h3("K-means Clustering"),
         selectInput("dataset_kmc", label = h5("Choose a Data Set for Analysis"),
                     choices = c("None")),
         
         # Display this only if the dataset input is not "None"
         conditionalPanel(condition = "input.dataset_kmc != 'None' ",
          numericInput('clusters', 'Number of Clusters', 3,
                      min = 2),
          selectInput("plotX_kmc", label = h5("Variable for X-axis"),
                     choices = NULL),
          selectInput("plotY_kmc", label = h5("Variable for Y-axis"),
                     choices = NULL),
          selectInput("dims", label = h5("Dimensions"),
                     choices = NULL, multiple=TRUE),
          numericInput('iter', 'Maximum Number of Iterations', 10,
                      min = 1),
          conditionalPanel(condition = "input.dims != null",
            actionButton("ok_kmc",label = h5("Ok")),
            actionButton("reset_kmc", label=h5("Reset")),
            actionButton("save_kmc",label = h5("Save"))
          )
           
        )
      
         
       ),

       mainPanel(
         htmlOutput("kmc_fn"),
         plotOutput("kmeans"),
         DT::dataTableOutput("kmeans_centers")
       )
      )
    ),
    
    tabPanel("PCA Analysis",
      sidebarLayout(
       sidebarPanel(
         h3("Principle Component Analysis"),
         selectInput("dataset_pca", label = h5("Choose a Data Set for Analysis"),
                     choices = c("None")),
         
         # Display this only if the dataset input is not "None" 
         conditionalPanel(condition = "input.dataset_pca != 'None'",
            selectInput("dims_pca", label = h5("Dimensions"), choices = NULL, multiple=TRUE),
            
            checkboxInput("normalize", h5("Normalize Data"),value=TRUE),
            
            conditionalPanel(condition = "input.dims_pca != null",
              actionButton("ok_pca",label = h5("Ok")),
              actionButton("reset_pca", label=h5("Reset")),
              actionButton("save_pca",label = h5("Save Analysis"))
            )
         )
       ),
       
       mainPanel(
         conditionalPanel(condition = "input.dataset_pca != 'None'",
            htmlOutput("pca_fn"),
           conditionalPanel(condition = "input.dims_pca != null" ,
             div(style='overflow-x: scroll',tableOutput("pca_eig")),
             tags$hr(),
             plotOutput("pca_plots"),
             tags$hr(),
             div(style='overflow-x: scroll',DT::dataTableOutput("pca_table"))
           )
         )
       )
      )
    ),
    
    tabPanel("View Analysis",
      sidebarLayout(
      sidebarPanel(
        h3("View Saved Analysis"),
        selectInput("fn_kmc", label = h5("View Clustering Analysis"),
                   choices = c("None")),
        selectInput("fn_pca", label = h5("View Principle Component Analysis"),
                    choices = c("None")),

        # Display this only if the filename input is not None 
        conditionalPanel(condition = "input.fn_kmc != 'None' " ,
          selectInput("plotX_va", label = h5("Variable for X-axis"),
                      choices = NULL),
          selectInput("plotY_va", label = h5("Variable for Y-axis"),
                      choices = NULL)
         )
        
       ),
       
       mainPanel(
         htmlOutput("viewSaved_fn"),
         conditionalPanel(condition = "input.fn_kmc != 'None'",
            plotOutput("viewSaved_kmc"),
            DT::dataTableOutput("viewSaved_kmc_centers")
         ),
         conditionalPanel(condition = "input.fn_pca != 'None'" ,
            div(style='overflow-x: scroll',tableOutput("viewSaved_pca_eig")),
            tags$hr(),
            plotOutput("viewSaved_pca_plots"),
            tags$hr(),
            DT::dataTableOutput("viewSaved_pca_table")
         )
       )
      )
    )
    
    
  ) #navbar
  
)) #shinyUI


