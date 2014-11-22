// ------------------------------------------------
// ---- Modal

var BootstrapModal = React.createClass(
{
	// The following two methods are the only places we need to
	// integrate with Bootstrap or jQuery!
	componentDidMount: function()
	{
		// When the component is added, turn it into a modal
		$(this.getDOMNode())
			.modal({backdrop: 'static', keyboard: false, show: false})
	},

	componentWillUnmount: function()
	{
		$(this.getDOMNode()).off('hidden', this.handleHidden);
	},

	close: function()
	{
		$(this.getDOMNode()).modal('hide');
	},

	open: function()
	{
		$(this.getDOMNode()).modal('show');
	},

	render: function()
	{
		var confirmButton = null;
		var cancelButton = null;

		if (this.props.confirm) {
			confirmButton = (
				<BootstrapButton
					onClick={this.handleConfirm}
					className="btn-primary"
				>
					{this.props.confirm}
				</BootstrapButton>
			);
		}
		if (this.props.cancel) {
			cancelButton = (
				<BootstrapButton
				    onClick={this.handleCancel}
				    className="btn-default"
				    dataKeyboard="true"
				>
					{this.props.cancel}
				</BootstrapButton>
			);
		}

        var modalBodyStyle = {
          backgroundColor: 'black',
          color: 'white',
          fontFamily: 'Courier New',
          maxHeight: 'calc(100vh - 150px)',
          overflowY: 'auto',
          overflowX: 'auto',
          whiteSpace: 'pre',
        };

        var modalDialogStyle = {
            width: '90%',
        };

		return (
			<div className="modal fade" tabIndex='-1'>
				<div className="modal-dialog modal-lg" style={modalDialogStyle}>
					<div className="modal-content">
						<div className="modal-header">
							<button
								type="button"
								className="close"
								dataDismiss="modal"
								onClick={this.handleCancel}>
								&times;
							</button>
							<h3>{this.props.title}</h3>
						</div>

						<div className="modal-body" style={modalBodyStyle}>
                            {this.props.children}
						</div>

						<div className="modal-footer">
							{cancelButton}
							{confirmButton}
						</div>
					</div>
				</div>
			</div>
		);
	},

	handleCancel: function() {
		if (this.props.onCancel) {
			this.props.onCancel();
		}
	},

	handleConfirm: function() {
		if (this.props.onConfirm) {
			this.props.onConfirm();
		}
	}
});


// ------------------------------------------------
// ---- Bootstrap Button

var BootstrapButton = React.createClass(
{
    render: function() {
        return (
            <a
                {...this.props}
                href="javascript:;"
                role="button"
                className={(this.props.className || '') + ' btn'}
                data-keyboard="true"
            />
        );
    }
});

// ------------------------------------------------
// ---- EventSystem

var EventSystem = (function() {
    var self = this;

    self.queue = {};

    return {
        fire: function (event, data)
        {
            var queue = self.queue[event];

            if (typeof queue === 'undefined')
            {
                return false;
            }

            jQuery.each( queue, function( key, method )
            {
                (method)(data);
            });

            return true;
        },

        listen: function(event, callback)
        {
            if (typeof self.queue[event] === 'undefined')
            {
                self.queue[event] = [];
            }

            self.queue[event].push(callback);
        }
    };
}());

// ------------------------------------------------
// ---- Test Table

var TestsTable = React.createClass(
{
    getInitialState: function() {
        return {data: [], selected: { name: '', id: null}};
    },

    loadFromServer: function()
    {
        if (this.state.selected.id)
        {
            $.ajax(
            {
                url: this.props.url + this.state.selected.id,

                dataType: 'json',

                success: function(data) {
                    this.setState({data: data});
                }.bind(this),

                error: function(xhr, status, err) {
                    console.error(this.props.url, status, err.toString());
                }.bind(this)
            });
        }
    },

    componentDidMount: function()
    {
        this.loadFromServer();

        setInterval(this.loadFromServer, this.props.pollInterval);

        EventSystem.listen('selected.changed', this.selectedChanged);
    },

    selectedChanged: function(event)
    {
        this.setState({selected: event.selected});

        this.loadFromServer();
    },

    render: function()
    {
        return (
            <TestList data={this.state.data} />
        );
    }
});

// ------------------------------------------------
// ---- Test List

var TestList = React.createClass(
{
    getInitialState: function()
    {
        return {data: [], selected: { name: '', id: null}};
    },

    componentDidMount: function()
    {
        EventSystem.listen('selected.changed', this.selectedChanged);
    },

    selectedChanged: function(event)
    {
        this.setState({selected: event.selected});
    },

    toogleAll: function(event, whatever)
    {
        {{--jQuery('.testCheckbox').prop('checked', event.target.checked);--}}

        console.log(this.state.selected.id);

        $.ajax({
            url: '/tests/enable/'+event.target.checked+'/'+this.state.selected.id,

            dataType: 'json',

            success: function(data)
            {
                jQuery('.testCheckbox').prop('checked', data.state);
            }.bind([this, event]),

            error: function(xhr, status, err)
            {
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },

    render: function()
    {
        var testNodes = this.props.data.map(function (test)
        {
            return (
                <TestRow key={test.id} test={test} projectId={this.state.selected.id} />
            );
        }, this);

        return (
            <div>
                <h2>{this.state.selected.name} - Tests</h2>

                <table className="table">
                    <thead>
                        <tr>
                            <th>
                                <input
                                    type="checkbox"
                                    title="Mark to enable test"
                                    onClick={this.toogleAll}
                                />
                            </th>
                            <th width="70%">Test</th>
                            <th>Last Run</th>
                            <th>State</th>
                            <th>Log</th>
                        </tr>
                    </thead>

                    <tbody id="#tests-table">
                        {testNodes}
                    </tbody>
                </table>
			</div>
        );
    }
});

// ------------------------------------------------
// ---- TestRow

var TestRow = React.createClass(
{
    toogleOne: function(event, whatever)
    {
        $.ajax({
            url: '/tests/enable/'+event.target.checked+'/'+this.props.projectId+'/'+this.props.test.id,

            dataType: 'json',

            error: function(xhr, status, err)
            {
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },

    render: function()
    {
        return (
            <tr key={this.props.test.id}>
                <td>
                    <input
                        type="checkbox"
                        title="Mark to enable test"
                        className="testCheckbox"
                        onClick={this.toogleOne}
                        checked={this.props.test.enabled}
                    />
                </td>
                <td>{this.props.test.name}</td>
                <td>{this.props.test.updated_at}</td>
                <td><State type={this.props.test.state} /></td>
                <td><LogButton type={this.props.test.state} log={this.props.test.log} name={this.props.test.name} /></td>
            </tr>
        );
    }
});


// ------------------------------------------------
// ---- State

var State = React.createClass(
{
    render: function()
    {
        var color;

        if (this.props.type == 'running')
        {
           color = 'info';
        }
        else if (this.props.type == 'ok')
        {
           color = 'success';
        }
        else if (this.props.type == 'failed')
        {
           color = 'danger';
        }
        else if (this.props.type == 'queued')
        {
           color = 'default';
        }

        return (
			<span className={"label label-"+color}>{this.props.type}</span>
        );
    }
});

// ------------------------------------------------
// ---- Log Button

var LogButton = React.createClass(
{
    render: function()
    {
        if (this.props.type == 'failed')
        {
            var modal = null;

    		body = React.DOM.div({ dangerouslySetInnerHTML:
    		{
                __html: this.props.log
            }});

            modal = (
                <BootstrapModal
                    ref="modal"
                    confirm="Close"
                    onConfirm={this.closeModal}
                    onCancel={this.closeModal}
                    title={this.props.name}
                >
                    {body}
                </BootstrapModal>
            );

            return (
                <div className="example">
                    {modal}

                    <button
                        type="button"
                        className="btn btn-xs btn-primary"
                        onClick={this.openModal}
                        data-keyboard="true"
                    >
                        Show
                    </button>
                </div>
           );
        }

        return false;
    },

    openModal: function()
    {
        this.refs.modal.open();
    },

    closeModal: function()
    {
        this.refs.modal.close();
    }

});

// ------------------------------------------------
// ---- Projects

var ProjectsMenu = React.createClass(
{
    getInitialState: function()
    {
        return {data: [], selected: { name: '', id: null}};
    },

    loadFromServer: function()
    {
        $.ajax({
            url: this.props.url,

            dataType: 'json',

            success: function(data)
            {
                this.setState({data: data});
                EventSystem.fire('selected.changed', { selected: { name: data[0].name, id: data[0].id }});
            }.bind(this),

            error: function(xhr, status, err)
            {
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },

    componentDidMount: function()
    {
        this.loadFromServer();
    },

    render: function()
    {
        return (
            <ProjectsMenuItems data={this.state.data} />
        );
    }
});

// ------------------------------------------------
// ---- Menu Items

var ProjectsMenuItems = React.createClass(
{
    getInitialState: function()
    {
        return {data: [], selected: { name: '', id: null}};
    },

    componentDidMount: function()
    {
        EventSystem.listen('selected.changed', this.selectedChanged);
    },

    selectedChanged: function(event)
    {
        this.setState({selected: event.selected});
    },

    handleClick: function(name, id)
    {
        EventSystem.fire('selected.changed', { selected: { name: name, id: id }});
    },

    render: function()
    {
        var nodes = [];

        for (index = 0; index < this.props.data.length; ++index)
        {
            project = this.props.data[index];

            nodes.push(
                <li
                    key={project.id}
                    className={project.id == this.state.selected.id ? 'active' : ''}
                    onClick={this.handleClick.bind(this, project.name, project.id)}
                >
                    <a href="#">{project.name}</a>
                </li>
            );
        }

        return (
            <ul className="nav nav-sidebar">
                {nodes}
            </ul>
        );
    },

});

// ------------------------------------------------
// ---- Rendering

React.render(
    <TestsTable url={"/tests/all/"} pollInterval={2000}/>,
    document.getElementById('table-container')
);

React.render(
    <ProjectsMenu url="/projects"/>,
    document.getElementById('projects')
);

